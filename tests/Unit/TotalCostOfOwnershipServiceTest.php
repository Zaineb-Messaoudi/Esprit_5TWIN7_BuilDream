<?php

namespace Tests\Unit;

use App\Models\Equipment;
use App\Models\Rental;
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
        $equipment = Equipment::factory()->create();
        $this->maintenance($equipment->id, 40);
        $this->maintenance($equipment->id, 60);
        Rental::factory()->create(['equipment_id' => $equipment->id, 'total_amount' => 250]);
        Rental::factory()->create(['equipment_id' => $equipment->id, 'total_amount' => 50]);

        $this->assertSame([
            'maintenance_costs' => 100.0,
            'rental_revenue' => 300.0,
            'net_margin' => 200.0,
            'cost_revenue_ratio' => 0.3333,
        ], app(TotalCostOfOwnershipService::class)->calculate($equipment->id));
    }

    public function test_zero_revenue_does_not_divide_by_zero_and_negative_margin_is_preserved(): void
    {
        $equipment = Equipment::factory()->create();
        $this->maintenance($equipment->id, 120);
        $withoutRevenue = app(TotalCostOfOwnershipService::class)->calculate($equipment->id);
        Rental::factory()->create(['equipment_id' => $equipment->id, 'total_amount' => 20]);
        $negativeMargin = app(TotalCostOfOwnershipService::class)->calculate($equipment->id);

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

