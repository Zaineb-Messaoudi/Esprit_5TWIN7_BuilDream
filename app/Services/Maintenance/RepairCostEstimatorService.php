<?php

namespace App\Services\Maintenance;

use App\Models\Damage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class RepairCostEstimatorService
{
    /** @return array{average: float, median: float, minimum: float, maximum: float, sample_size: int, source: string} */
    public function estimate(?int $categoryId, string $severity): array
    {
        $base = Damage::query()->where('severity', $severity)->whereNotNull('repair_cost')->where('repair_cost', '>', 0);
        $costs = $this->forCategory(clone $base, $categoryId);
        $source = 'category';

        if ($costs->isEmpty()) {
            $costs = $base->pluck('repair_cost')->map(fn ($cost) => (float) $cost);
            $source = 'global';
        }

        if ($costs->isEmpty()) {
            return [
                'average' => (float) config('maintenance.repair_estimation.default_cost', 100),
                'median' => (float) config('maintenance.repair_estimation.default_cost', 100),
                'minimum' => (float) config('maintenance.repair_estimation.default_minimum', 80),
                'maximum' => (float) config('maintenance.repair_estimation.default_maximum', 120),
                'sample_size' => 0,
                'source' => 'default',
            ];
        }

        $sorted = $costs->sort()->values();
        $middle = intdiv($sorted->count(), 2);
        $median = $sorted->count() % 2 ? $sorted[$middle] : ($sorted[$middle - 1] + $sorted[$middle]) / 2;

        return [
            'average' => round(max(0.01, $sorted->average()), 2),
            'median' => round(max(0.01, $median), 2),
            'minimum' => round(max(0.01, $sorted->min()), 2),
            'maximum' => round(max(0.01, $sorted->max()), 2),
            'sample_size' => $sorted->count(),
            'source' => $source,
        ];
    }

    private function forCategory(Builder $query, ?int $categoryId): \Illuminate\Support\Collection
    {
        if ($categoryId === null || ! Schema::hasColumn('equipment', 'category_id')) {
            return collect();
        }

        return $query->whereIn('equipment_id', fn ($equipment) => $equipment
            ->select('id')->from('equipment')->where('category_id', $categoryId))
            ->pluck('repair_cost')->map(fn ($cost) => (float) $cost);
    }
}
