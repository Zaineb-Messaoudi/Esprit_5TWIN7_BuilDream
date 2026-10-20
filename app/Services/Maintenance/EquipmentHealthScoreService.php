<?php

namespace App\Services\Maintenance;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EquipmentHealthScoreService
{
    /**
     * Return score, badge state, and component scores for an equipment-like model.
     * The model must expose an id and may expose created_at.
     *
     * @return array{score: int, level: string, color: string, components: array<string, float>}
     */
    public function score(Model $equipment): array
    {
        $equipmentId = $equipment->getKey();
        $createdAt = $equipment->created_at ?? null;
        $ageYears = $createdAt ? max(0, Carbon::now()->diffInDays($createdAt) / 365.25) : 0.0;

        return $this->calculate([
            'age_years' => $ageYears,
            'damage_severities' => $this->damageSeverities($equipmentId),
            'days_since_last_maintenance' => $this->daysSinceLastMaintenance($equipmentId),
            'rental_count' => $this->rentalCount($equipmentId),
        ]);
    }

    /** @param array{age_years?: float|int, damage_severities?: list<string>, days_since_last_maintenance?: int|null, rental_count?: int} $metrics */
    public function calculate(array $metrics): array
    {
        return $this->result($this->components($metrics));
    }

    /**
     * Pure calculation entry point for deterministic unit tests and non-ORM callers.
     * Missing maintenance history is represented by null days.
     *
     * @param array{age_years?: float|int, damage_severities?: list<string>, days_since_last_maintenance?: int|null, rental_count?: int} $metrics
     * @return array<string, float>
     */
    public function components(array $metrics): array
    {
        $agePointsPerYear = (float) config('maintenance.health_score.age.points_lost_per_year', 5);
        $severityPoints = (array) config('maintenance.health_score.damage.points_lost_by_severity', []);
        $defaultSeverity = (string) config('maintenance.health_score.damage.default_severity', 'moderate');
        $maintenancePointsPerDay = (float) config('maintenance.health_score.maintenance.points_lost_per_day', 0.20);
        $graceDays = (int) config('maintenance.health_score.maintenance.never_maintained_under_days', 30);
        $rentalPoints = (float) config('maintenance.health_score.rentals.points_lost_per_rental', 2);

        $ageYears = max(0, (float) ($metrics['age_years'] ?? 0));
        $damageSeverities = $metrics['damage_severities'] ?? [];
        $damagePenalty = 0.0;
        foreach ($damageSeverities as $severity) {
            $damagePenalty += (float) ($severityPoints[$severity] ?? $severityPoints[$defaultSeverity] ?? 20);
        }

        $daysSinceMaintenance = $metrics['days_since_last_maintenance'] ?? null;
        if ($daysSinceMaintenance === null) {
            $maintenanceScore = $ageYears <= ($graceDays / 365.25) ? 100.0 : 0.0;
        } else {
            $maintenanceScore = 100 - (max(0, $daysSinceMaintenance) * $maintenancePointsPerDay);
        }

        return [
            'age' => $this->clamp(100 - ($ageYears * $agePointsPerYear)),
            'damage' => $this->clamp(100 - $damagePenalty),
            'maintenance' => $this->clamp($maintenanceScore),
            'rentals' => $this->clamp(100 - (max(0, (int) ($metrics['rental_count'] ?? 0)) * $rentalPoints)),
        ];
    }

    /** @param array<string, float> $components */
    private function result(array $components): array
    {
        $weights = (array) config('maintenance.health_score.weights', []);
        $weightTotal = array_sum($weights);
        $weightedScore = 0.0;
        if ($weightTotal > 0) {
            foreach ($components as $component => $value) {
                $weightedScore += $value * (float) ($weights[$component] ?? 0);
            }
            $weightedScore /= $weightTotal;
        }
        $score = (int) round($this->clamp($weightedScore));
        $healthyMinimum = (int) config('maintenance.health_score.badges.healthy_minimum', 75);
        $warningMinimum = (int) config('maintenance.health_score.badges.warning_minimum', 40);

        [$level, $color] = match (true) {
            $score >= $healthyMinimum => ['healthy', 'success'],
            $score >= $warningMinimum => ['warning', 'warning'],
            default => ['critical', 'error'],
        };

        return compact('score', 'level', 'color', 'components');
    }

    /** @return list<string> */
    private function damageSeverities(mixed $equipmentId): array
    {
        if (! Schema::hasTable('inspections') || ! Schema::hasColumn('inspections', 'damage_detected')) {
            return [];
        }

        $query = \App\Models\Inspection::query()->where('equipment_id', $equipmentId)->where('damage_detected', true);
        if (Schema::hasColumn('inspections', 'severity')) {
            return $query->pluck('severity')->map(fn ($severity) => (string) $severity)->all();
        }

        return array_fill(0, $query->count(), (string) config('maintenance.health_score.damage.default_severity', 'moderate'));
    }

    private function daysSinceLastMaintenance(mixed $equipmentId): ?int
    {
        if (! Schema::hasTable('maintenances')) {
            return null;
        }

        $lastMaintenanceDate = \App\Models\Maintenance::query()
            ->where('equipment_id', $equipmentId)
            ->orderByDesc('start_date')
            ->value('end_date');

        if (! $lastMaintenanceDate) {
            $lastMaintenanceDate = \App\Models\Maintenance::query()
                ->where('equipment_id', $equipmentId)
                ->orderByDesc('start_date')
                ->value('start_date');
        }

        if (! $lastMaintenanceDate) {
            return null;
        }

        return (int) max(0, floor(Carbon::now()->diffInDays($lastMaintenanceDate)));
    }

    private function rentalCount(mixed $equipmentId): int
    {
        if (! Schema::hasTable('rentals') || ! Schema::hasColumn('rentals', 'equipment_id')) {
            return 0;
        }

        return (int) DB::table('rentals')->where('equipment_id', $equipmentId)->count();
    }

    private function clamp(float $value): float
    {
        return min(100.0, max(0.0, $value));
    }
}
