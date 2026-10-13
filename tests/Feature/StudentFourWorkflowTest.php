<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\RentalContract;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentFourWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_reservation_owner_approval_and_verified_payment_create_rental_contract_and_invoice(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::BUYER, 'role_setup_completed' => true]);
        $owner = User::factory()->create(['role' => UserRole::OWNER, 'role_setup_completed' => true]);
        $admin = User::factory()->create(['role' => UserRole::ADMIN, 'role_setup_completed' => true]);
        $equipment = Equipment::factory()->create([
            'owner_id' => $owner->id,
            'category_id' => Category::factory()->create()->id,
            'approval_status' => 'published',
            'status' => 'available',
            'price_per_day' => 30,
        ]);

        $this->actingAs($buyer)->post(route('booking.reservations.store'), [
            'equipment_id' => $equipment->id,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'total_amount' => 1,
        ])->assertRedirect();

        $reservation = Reservation::firstOrFail();
        $this->assertSame(90.0, (float) $reservation->total_amount);
        $this->assertSame('pending', $reservation->status);
        $this->assertDatabaseHas('equipment', ['id' => $equipment->id]);
        $this->assertTrue($equipment->reservations()->whereKey($reservation->id)->exists());

        $this->actingAs($owner)->post(route('owner.reservations.decision', $reservation), [
            'decision' => 'approve',
        ])->assertRedirect();
        $this->assertDatabaseHas('reservations', ['id' => $reservation->id, 'status' => 'confirmed']);

        $this->actingAs($buyer)->post(route('booking.reservations.pay', $reservation), [
            'payment_method' => 'CARD',
        ])->assertRedirect(route('front.confirmed', ['equipment' => $equipment->id, 'reservation' => $reservation->id]));

        $payment = Payment::where('reservation_id', $reservation->id)->firstOrFail();
        $this->assertSame('pending', $payment->status);
        $this->assertSame(107.1, (float) $payment->amount);
        $this->assertDatabaseMissing('rentals', ['reservation_id' => $reservation->id]);

        $this->actingAs($admin)->put(route('rental.payments.update', $payment), ['status' => 'paid'])
            ->assertRedirect(route('rental.payments.index'));

        $rental = Rental::where('reservation_id', $reservation->id)->firstOrFail();
        $this->assertSame($equipment->id, $rental->equipment_id);
        $this->assertSame($buyer->id, $rental->user_id);
        $this->assertSame(107.1, (float) $rental->total_amount);
        $this->assertTrue(RentalContract::where('rental_id', $rental->id)->exists());
        $invoice = Invoice::where('reservation_id', $reservation->id)->firstOrFail();
        $this->assertSame('paid', $invoice->status);
        $this->assertSame(90.0, (float) $invoice->subtotal);
        $this->assertSame(17.1, (float) $invoice->tax);

        $this->actingAs($buyer)->get(route('front.my-reservations'))->assertOk()->assertSee($reservation->reference);
        $this->actingAs($owner)->get(route('front.my-reservations'))->assertOk()->assertSee($reservation->reference);
        $this->actingAs($buyer)->get(route('front.my-rentals'))->assertOk()->assertSee($rental->reference);
        $this->actingAs($owner)->get(route('front.my-rentals'))->assertOk()->assertSee($rental->reference);
        $this->actingAs($buyer)->get(route('front.my-contract'))->assertOk()->assertSee($rental->contract->contract_number);
        $this->actingAs($buyer)->get(route('front.my-payments'))->assertOk()->assertSee($invoice->invoice_number);
    }

    public function test_overlapping_reservation_is_rejected(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::BUYER, 'role_setup_completed' => true]);
        $equipment = Equipment::factory()->create(['approval_status' => 'published', 'status' => 'available']);
        Reservation::factory()->create([
            'equipment_id' => $equipment->id,
            'user_id' => $buyer->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'status' => 'confirmed',
        ]);

        $this->actingAs($buyer)->post(route('booking.reservations.store'), [
            'equipment_id' => $equipment->id,
            'start_date' => now()->addDays(4)->toDateString(),
            'end_date' => now()->addDays(6)->toDateString(),
        ])->assertSessionHasErrors('start_date');
    }

    public function test_owner_cannot_approve_another_owners_reservation_and_paid_records_cannot_be_deleted(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::BUYER, 'role_setup_completed' => true]);
        $owner = User::factory()->create(['role' => UserRole::OWNER, 'role_setup_completed' => true]);
        $otherOwner = User::factory()->create(['role' => UserRole::OWNER, 'role_setup_completed' => true]);
        $equipment = Equipment::factory()->create(['owner_id' => $owner->id]);
        $reservation = Reservation::factory()->create([
            'equipment_id' => $equipment->id,
            'user_id' => $buyer->id,
            'status' => 'pending',
        ]);

        $this->actingAs($otherOwner)->post(route('owner.reservations.decision', $reservation), [
            'decision' => 'approve',
        ])->assertForbidden();

        $this->actingAs($buyer)->get(route('front.buyer-reservation-detail', $reservation->reference))
            ->assertOk()->assertSee($reservation->reference);
        $this->actingAs($otherOwner)->get(route('front.buyer-reservation-detail', $reservation->reference))
            ->assertForbidden();
    }
}
