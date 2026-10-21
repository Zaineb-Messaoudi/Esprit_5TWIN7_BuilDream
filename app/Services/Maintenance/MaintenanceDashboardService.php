<?php

namespace App\Services\Maintenance;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MaintenanceDashboardService
{
    public function monthlyCosts(): array
    {
        $monthExpression = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', start_date)"
            : "DATE_FORMAT(start_date, '%Y-%m')";

        $rows = DB::table('maintenances')
            ->selectRaw("{$monthExpression} as period, SUM(cost) as total")
            ->groupByRaw($monthExpression)
            ->orderBy('period')
            ->get();

        return $this->chart($rows->pluck('period')->all(), $rows->pluck('total')->map(fn ($value) => round((float) $value, 2))->all(), __('Maintenance costs'));
    }

    public function failuresByCategory(): array
    {
        $rows = $this->damageCategoryQuery()
            ->selectRaw($this->categorySelect().', COUNT(damages.id) as total')
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        return $this->chart($rows->pluck('category')->all(), $rows->pluck('total')->map(fn ($value) => (int) $value)->all(), __('Failures'));
    }

    public function averageFailureInterval(): array
    {
        $rows = $this->damageCategoryQuery()
            ->selectRaw($this->categorySelect().', damages.equipment_id, COUNT(damages.id) as failures, MIN(damages.created_at) as first_failure, MAX(damages.created_at) as last_failure')
            ->groupBy('category', 'damages.equipment_id')
            ->havingRaw('COUNT(damages.id) > 1')
            ->get();

        $aggregates = $rows->groupBy('category')->map(function ($categoryRows): float {
            $intervals = $categoryRows->sum(fn ($row) => max(0, (int) $row->failures - 1));
            if ($intervals === 0) {
                return 0.0;
            }

            $days = $categoryRows->sum(fn ($row) => Carbon::parse($row->first_failure)->diffInDays(Carbon::parse($row->last_failure)));

            return round($days / $intervals, 2);
        })->sortKeys();

        return $this->chart($aggregates->keys()->all(), $aggregates->values()->all(), __('Average days between failures'));
    }

    private function damageCategoryQuery(): Builder
    {
        $query = DB::table('damages')->join('equipment', 'damages.equipment_id', '=', 'equipment.id');
        if ($this->hasCategories()) {
            $query->leftJoin('categories', 'equipment.category_id', '=', 'categories.id');
        }

        return $query;
    }

    private function categorySelect(): string
    {
        return $this->hasCategories() ? "COALESCE(categories.name, 'Uncategorized') as category" : "'Uncategorized' as category";
    }

    private function hasCategories(): bool
    {
        return Schema::hasTable('categories') && Schema::hasColumn('equipment', 'category_id');
    }

    private function chart(array $labels, array $data, string $label): array
    {
        return ['labels' => array_values($labels), 'datasets' => [['label' => $label, 'data' => array_values($data)]]];
    }
}
