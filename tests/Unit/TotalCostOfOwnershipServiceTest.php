<?php

namespace Tests\Unit;

use App\Services\Maintenance\TotalCostOfOwnershipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TotalCostOfOwnershipServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_calculation_is_exact_for_known_data(): void
    {
        $this->maintenance(1, 40);
        $this->maintenance(1, 60);
        DB::table('rentals')->insert([['equipment_id' => 1, 'total_amount' => 250], ['equipment_id' => 1, 'total_amount' => 50]]);

        $this->assertSame([
            'maintenance_costs' => 100.0,
            'rental_revenue' => 300.0,
            'net_margin' => 200.0,
            'cost_revenue_ratio' => 0.3333,
        ], app(TotalCostOfOwnershipService::class)->calculate(1));
    }

    public function test_zero_revenue_does_not_divide_by_zero_and_negative_margin_is_preserved(): void
    {
        $this->maintenance(2, 120);
        $withoutRevenue = app(TotalCostOfOwnershipService::class)->calculate(2);
        DB::table('rentals')->insert(['equipment_id' => 2, 'total_amount' => 20]);
        $negativeMargin = app(TotalCostOfOwnershipService::class)->calculate(2);

        $this->assertNull($withoutRevenue['cost_revenue_ratio']);
        $this->assertSame(-120.0, $withoutRevenue['net_margin']);
        $this->assertSame(-100.0, $negativeMargin['net_margin']);
        $this->assertSame(6.0, $negativeMargin['cost_revenue_ratio']);
    }

    private function maintenance(int $equipmentId, float $cost): void
    {
        DB::table('maintenances')->insert([
            'equipment_id' => $equipmentId,
            'start_date' => '2026-01-01',
            'reason' => 'TCO test',
            'cost' => $cost,
            'status' => 'completed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

