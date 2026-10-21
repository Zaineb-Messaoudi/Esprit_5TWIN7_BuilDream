<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Equipment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReservationPaymentBackendValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_internal_reservation_endpoint_rejects_unpublished_equipment_and_invalid_date_format(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::BUYER, 'role_setup_completed' => true]);
        $equipment = Equipment::factory()->create(['approval_status' => 'pending', 'status' => 'available']);

        $this->actingAs($buyer)
            ->from(route('rental.reservations.create'))
            ->post(route('rental.reservations.store'), [
                'equipment_id' => $equipment->id,
                'start_date' => now()->addDays(2)->toDateString(),
                'end_date' => now()->addDays(3)->toDateString(),
            ])
            ->assertSessionHasErrors('equipment_id');

        $equipment->update(['approval_status' => 'published']);
        $this->actingAs($buyer)
            ->from(route('rental.reservations.create'))
            ->post(route('rental.reservations.store'), [
                'equipment_id' => $equipment->id,
                'start_date' => now()->addDays(2)->toDateString().' 12:00:00',
                'end_date' => now()->addDays(3)->toDateString(),
            ])
            ->assertSessionHasErrors('start_date');
    }

    public function test_admin_payment_rejects_amount_above_balance_even_when_pending(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN, 'role_setup_completed' => true]);
        $reservation = Reservation::factory()->create([
            'total_amount' => 100,
            'status' => 'confirmed',
        ]);

        $this->actingAs($admin)
            ->post(route('rental.payments.store'), [
                'reservation_id' => $reservation->id,
                'amount' => '1000.00',
                'payment_date' => now()->format('Y-m-d\\TH:i'),
                'status' => 'pending',
                'payment_method' => 'BANK_TRANSFER',
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('payments', ['reservation_id' => $reservation->id]);
    }

    public function test_payment_request_rejects_fractional_precision_beyond_currency_scale_and_future_date(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN, 'role_setup_completed' => true]);
        $reservation = Reservation::factory()->create(['status' => 'confirmed']);

        $this->actingAs($admin)
            ->from(route('rental.payments.create'))
            ->post(route('rental.payments.store'), [
                'reservation_id' => $reservation->id,
                'amount' => '10.123',
                'payment_date' => now()->addDay()->format('Y-m-d\\TH:i'),
                'status' => 'pending',
                'payment_method' => 'CASH',
            ])
            ->assertSessionHasErrors(['amount', 'payment_date']);
    }

    public function test_api_reservation_returns_server_calculated_total(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::BUYER, 'role_setup_completed' => true]);
        $equipment = Equipment::factory()->create([
            'approval_status' => 'published',
            'status' => 'available',
            'price_per_day' => 20,
        ]);
        Sanctum::actingAs($buyer);

        $this->postJson('/api/reservations', [
            'equipment_id' => $equipment->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
        ])
            ->assertCreated()
            ->assertJsonPath('reservation.total_amount', 40);
    }
}
