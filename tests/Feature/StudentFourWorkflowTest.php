<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\Invoice;
use App\Models\Rental;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentFourWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_create_reservation_pay_and_receive_rental_and_invoice(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::BUYER]);
        $owner = User::factory()->create(['role' => UserRole::OWNER]);
        $equipment = Equipment::factory()->create([
            'owner_id' => $owner->id,
            'category_id' => Category::factory()->create()->id,
        ]);

        $reservationResponse = $this->actingAs($buyer)->post(route('rental.reservations.store'), [
            'equipment_id' => $equipment->id,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'total_amount' => 120,
        ]);

        $reservationResponse->assertRedirect(route('rental.reservations.index'));
        $reservation = Reservation::firstOrFail();
        $this->assertTrue($equipment->reservations()->whereKey($reservation->id)->exists());

        $this->actingAs($buyer)->post(route('rental.payments.store'), [
            'reservation_id' => $reservation->id,
            'amount' => 120,
            'payment_date' => now()->toDateTimeString(),
            'status' => 'paid',
            'payment_method' => 'CARD',
        ])->assertRedirect(route('rental.payments.index'));

        $this->assertDatabaseHas('rentals', ['reservation_id' => $reservation->id, 'equipment_id' => $equipment->id]);
        $this->assertSame($reservation->id, Rental::firstOrFail()->reservation->id);

        $this->actingAs($buyer)->post(route('rental.invoices.store'), [
            'reservation_id' => $reservation->id,
            'issue_date' => now()->toDateString(),
            'subtotal' => 120,
            'status' => 'unpaid',
        ])->assertRedirect(route('rental.invoices.index'));

        $this->assertTrue(Invoice::where('reservation_id', $reservation->id)->exists());
    }

    public function test_overlapping_reservation_is_rejected(): void
    {
        $buyer = User::factory()->create(['role' => UserRole::BUYER]);
        $equipment = Equipment::factory()->create();
        Reservation::factory()->create([
            'equipment_id' => $equipment->id,
            'user_id' => $buyer->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'status' => 'confirmed',
        ]);

        $this->actingAs($buyer)->post(route('rental.reservations.store'), [
            'equipment_id' => $equipment->id,
            'start_date' => now()->addDays(4)->toDateString(),
            'end_date' => now()->addDays(6)->toDateString(),
            'total_amount' => 100,
        ])->assertSessionHasErrors('start_date');
    }

    public function test_reservation_payment_and_invoice_update_and_delete_actions_work(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $buyer = User::factory()->create(['role' => UserRole::BUYER]);
        $equipment = Equipment::factory()->create();
        $reservation = Reservation::factory()->create([
            'user_id' => $buyer->id,
            'equipment_id' => $equipment->id,
            'status' => 'pending',
        ]);
        $payment = $reservation->payments()->create([
            'amount' => 50,
            'payment_date' => now(),
            'transaction_reference' => 'PAY-TEST-'.uniqid(),
            'status' => 'pending',
            'payment_method' => 'CARD',
        ]);
        $invoice = $reservation->invoice()->create([
            'invoice_number' => 'INV-TEST-'.uniqid(),
            'issue_date' => now(),
            'subtotal' => 50,
            'tax' => 9.5,
            'total' => 59.5,
            'status' => 'unpaid',
        ]);

        $this->actingAs($buyer)->put(route('rental.reservations.update', $reservation), [
            'equipment_id' => $equipment->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(4)->toDateString(),
            'total_amount' => 75,
        ])->assertRedirect(route('rental.reservations.index'));

        $this->actingAs($admin)->put(route('rental.payments.update', $payment), ['status' => 'paid'])
            ->assertRedirect(route('rental.payments.index'));
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'paid']);

        $this->actingAs($admin)->put(route('rental.invoices.update', $invoice), [
            'issue_date' => now()->toDateString(), 'subtotal' => 80, 'status' => 'paid',
        ])->assertRedirect(route('rental.invoices.index'));

        $this->actingAs($buyer)->delete(route('rental.payments.destroy', $payment))
            ->assertRedirect(route('rental.payments.index'));
        $this->actingAs($buyer)->delete(route('rental.invoices.destroy', $invoice))
            ->assertRedirect(route('rental.invoices.index'));
        $this->actingAs($buyer)->delete(route('rental.reservations.destroy', $reservation))
            ->assertNoContent();
    }
}
