<?php

namespace App\Services\Maintenance;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TotalCostOfOwnershipService
{
    /** @return array{maintenance_costs: float, rental_revenue: float, net_margin: float, cost_revenue_ratio: float|null} */
    public function calculate(int $equipmentId): array
    {
        $costs = (float) DB::table('maintenances')->where('equipment_id', $equipmentId)->sum('cost');
        $table = (string) config('maintenance.tco.revenue_table', 'rentals');
        $equipmentColumn = (string) config('maintenance.tco.equipment_column', 'equipment_id');
        $amountColumn = (string) config('maintenance.tco.amount_column', 'total_amount');
        $revenue = Schema::hasTable($table) && Schema::hasColumn($table, $equipmentColumn) && Schema::hasColumn($table, $amountColumn)
            ? (float) DB::table($table)->where($equipmentColumn, $equipmentId)->sum($amountColumn)
            : 0.0;

        return [
            'maintenance_costs' => round($costs, 2),
            'rental_revenue' => round($revenue, 2),
            'net_margin' => round($revenue - $costs, 2),
            'cost_revenue_ratio' => $revenue > 0 ? round($costs / $revenue, 4) : null,
        ];
    }
}
