<?php

namespace App\Services\Maintenance;

use App\Enums\UserRole;
use App\Models\Maintenance;
use App\Models\User;
use App\Notifications\AbnormalMaintenanceCostNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;

class MaintenanceCostAnomalyService
{
    /** @param iterable<int, float|int|string> $history */
    public function zScore(float $cost, iterable $history): ?float
    {
        $values = collect($history)->map(fn ($value) => (float) $value)->values();
        if ($values->count() < (int) config('maintenance.cost_anomaly.minimum_history', 3)) {
            return null;
        }

        $mean = (float) $values->average();
        $variance = (float) $values->average(fn (float $value) => ($value - $mean) ** 2);
        $standardDeviation = sqrt($variance);

        return $standardDeviation > 0 ? ($cost - $mean) / $standardDeviation : null;
    }

    public function evaluateAndNotify(Maintenance $maintenance): ?float
    {
        $zScore = $this->zScore((float) $maintenance->cost, $this->historyFor($maintenance));
        if ($zScore === null || abs($zScore) <= (float) config('maintenance.cost_anomaly.z_score_threshold', 2)) {
            return $zScore;
        }

        $admins = User::query()->where('role', UserRole::ADMIN->value)->get();
        Notification::send($admins, new AbnormalMaintenanceCostNotification($maintenance, $zScore));

        return $zScore;
    }

    private function historyFor(Maintenance $maintenance): Collection
    {
        $query = Maintenance::query()->whereKeyNot($maintenance->getKey());
        $categoryId = Schema::hasColumn('equipment', 'category_id')
            ? $maintenance->equipment()->value('category_id')
            : null;

        if ($categoryId !== null) {
            $query->whereIn('equipment_id', fn ($equipment) => $equipment
                ->select('id')->from('equipment')->where('category_id', $categoryId));
        } else {
            $query->where('equipment_id', $maintenance->equipment_id);
        }

        return $query->pluck('cost');
    }
}
