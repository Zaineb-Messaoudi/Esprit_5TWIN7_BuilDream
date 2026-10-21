<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\Payment;
use App\Models\Reservation;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsRevenueTest extends TestCase
{
    use RefreshDatabase;

    public function test_top_equipment_revenue_uses_net_refunds_and_counts_reservations_not_installments(): void
    {
        $equipment = Equipment::factory()->create(['name' => 'Solar battery']);
        $reservations = Reservation::factory()->count(2)->create([
            'equipment_id' => $equipment->id,
        ]);

        Payment::factory()->create([
            'reservation_id' => $reservations[0]->id,
            'amount' => 100,
            'refunded_amount' => 40,
            'payment_date' => now(),
            'status' => 'partially_refunded',
        ]);
        Payment::factory()->create([
            'reservation_id' => $reservations[0]->id,
            'amount' => 50,
            'payment_date' => now(),
            'status' => 'paid',
        ]);
        Payment::factory()->create([
            'reservation_id' => $reservations[1]->id,
            'amount' => 60,
            'payment_date' => now(),
            'status' => 'paid',
        ]);

        $result = app(AnalyticsService::class)->getTopEquipment(
            start: today()->startOfDay(),
            end: today()->endOfDay(),
        );

        $this->assertSame(1, count($result));
        $this->assertSame($equipment->id, $result[0]['equipment_id']);
        $this->assertSame('Solar battery', $result[0]['equipment_name']);
        $this->assertEquals(170, $result[0]['total_revenue']);
        $this->assertSame(2, $result[0]['booking_count']);
        $this->assertEquals(85, $result[0]['avg_booking_value']);
    }
}
