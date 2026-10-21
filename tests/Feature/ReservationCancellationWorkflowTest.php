<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use App\Services\StripeConnectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Stripe\Refund;
use Tests\TestCase;

class ReservationCancellationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_future_stripe_payment_is_refunded_and_reservation_invoice_are_cancelled(): void
    {
        config(['services.stripe.secret' => 'sk_test_placeholder']);

        $buyer = User::factory()->create([
            'role' => UserRole::BUYER,
            'role_setup_completed' => true,
        ]);
        $reservation = Reservation::factory()->create([
            'user_id' => $buyer->id,
            'start_date' => today()->addDays(20),
            'status' => 'confirmed',
        ]);
        $payment = Payment::factory()->create([
            'reservation_id' => $reservation->id,
            'amount' => 35.7,
            'status' => 'paid',
            'payment_method' => 'CARD',
            'transaction_reference' => 'cs_test_reservation_refund',
        ]);
        Invoice::create([
            'reservation_id' => $reservation->id,
            'invoice_number' => 'INV-CANCEL-TEST',
            'issue_date' => today(),
            'subtotal' => 30,
            'tax' => 5.7,
            'total' => 35.7,
            'status' => 'partially_paid',
        ]);

        $this->mock(StripeConnectService::class)
            ->shouldReceive('createReservationRefund')
            ->once()
            ->with(
                'cs_test_reservation_refund',
                'reservation-'.$reservation->id.'-payment-'.$payment->id.'-refund-1',
                $payment->id,
                1142
            )
            ->andReturn(Refund::constructFrom([
                'id' => 're_test_reservation_refund',
                'status' => 'succeeded',
                'amount' => 1142,
                'payment_intent' => 'pi_test_reservation_refund',
            ]));

        $this->actingAs($buyer)
            ->post(route('booking.reservations.cancellation', $reservation), [
                'reason' => 'Plans changed before the trip.',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'cancelled',
            'cancellation_status' => 'approved',
        ]);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'refunded',
            'refund_status' => 'succeeded',
            'refunded_amount' => 35.69,
            'stripe_payment_intent_id' => 'pi_test_reservation_refund',
            'stripe_refund_id' => 're_test_reservation_refund',
        ]);
        $this->assertDatabaseHas('invoices', [
            'reservation_id' => $reservation->id,
            'status' => 'refunded',
        ]);
    }

    public function test_after_start_cash_refund_requires_admin_review(): void
    {
        $buyer = User::factory()->create([
            'role' => UserRole::BUYER,
            'role_setup_completed' => true,
        ]);
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'role_setup_completed' => true,
        ]);
        $reservation = Reservation::factory()->create([
            'user_id' => $buyer->id,
            'start_date' => today(),
            'status' => 'confirmed',
        ]);
        $payment = Payment::factory()->create([
            'reservation_id' => $reservation->id,
            'status' => 'paid',
            'payment_method' => 'CASH',
        ]);

        $this->actingAs($buyer)
            ->post(route('booking.reservations.cancellation', $reservation), [
                'reason' => 'Rental conditions have changed.',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'cancellation_requested',
            'cancellation_status' => 'requested',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.reservations.cancellation-decision', $reservation), [
                'decision' => 'approve',
                'manual_refund_confirmed' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'cancelled',
            'cancellation_status' => 'approved',
            'cancellation_reviewed_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'paid',
            'refund_status' => 'none',
            'refunded_amount' => 0,
        ]);
    }

    public function test_async_stripe_refund_webhook_finalizes_cancellation(): void
    {
        config([
            'services.stripe.webhook_secret' => 'whsec_test_reservation_refund',
            'services.stripe.tnd_to_checkout_rate' => 0.32,
        ]);

        $buyer = User::factory()->create([
            'role' => UserRole::BUYER,
            'role_setup_completed' => true,
        ]);
        $reservation = Reservation::factory()->create([
            'user_id' => $buyer->id,
            'start_date' => today()->addDays(15),
            'status' => 'cancellation_requested',
            'cancellation_status' => 'requested',
        ]);
        $payment = Payment::factory()->create([
            'reservation_id' => $reservation->id,
            'amount' => 35.7,
            'status' => 'paid',
            'stripe_payment_intent_id' => 'pi_test_async_refund',
            'stripe_refund_id' => 're_test_async_refund',
            'refund_target_amount' => 35.7,
            'refund_status' => 'pending',
        ]);

        $this->postSignedRefundEvent([
            'id' => 're_test_async_refund',
            'object' => 'refund',
            'status' => 'succeeded',
            'amount' => 1142,
            'payment_intent' => 'pi_test_async_refund',
        ])->assertOk();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'refunded',
            'refund_status' => 'succeeded',
        ]);
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'cancelled',
            'cancellation_status' => 'approved',
        ]);
    }

    public function test_cancellation_four_days_before_start_refunds_half_and_keeps_the_rest(): void
    {
        config(['services.stripe.secret' => 'sk_test_placeholder']);

        $buyer = User::factory()->create([
            'role' => UserRole::BUYER,
            'role_setup_completed' => true,
        ]);
        $reservation = Reservation::factory()->create([
            'user_id' => $buyer->id,
            'start_date' => today()->addDays(4),
            'status' => 'confirmed',
        ]);
        $payment = Payment::factory()->create([
            'reservation_id' => $reservation->id,
            'amount' => 100,
            'status' => 'paid',
            'payment_method' => 'CARD',
            'transaction_reference' => 'cs_test_partial_refund',
        ]);

        $this->mock(StripeConnectService::class)
            ->shouldReceive('createReservationRefund')
            ->once()
            ->with(
                'cs_test_partial_refund',
                'reservation-'.$reservation->id.'-payment-'.$payment->id.'-refund-1',
                $payment->id,
                1600
            )
            ->andReturn(Refund::constructFrom([
                'id' => 're_test_partial_refund',
                'status' => 'succeeded',
                'amount' => 1600,
                'payment_intent' => 'pi_test_partial_refund',
            ]));

        $this->actingAs($buyer)
            ->post(route('booking.reservations.cancellation', $reservation), [
                'reason' => 'The trip plans changed.',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'cancelled',
            'cancellation_refund_percentage' => 50,
            'cancellation_refund_total' => 50,
        ]);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'partially_refunded',
            'refund_target_amount' => 50,
            'refunded_amount' => 50,
        ]);
        $this->assertEquals(50.0, app(\App\Services\ReservationPaymentService::class)->amountPaid($reservation));
    }

    public function test_cancellation_on_start_date_has_zero_refund_and_requires_review(): void
    {
        $buyer = User::factory()->create([
            'role' => UserRole::BUYER,
            'role_setup_completed' => true,
        ]);
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'role_setup_completed' => true,
        ]);
        $reservation = Reservation::factory()->create([
            'user_id' => $buyer->id,
            'start_date' => today(),
            'status' => 'confirmed',
        ]);
        $payment = Payment::factory()->create([
            'reservation_id' => $reservation->id,
            'amount' => 100,
            'status' => 'paid',
            'payment_method' => 'CASH',
        ]);

        $this->actingAs($buyer)
            ->post(route('booking.reservations.cancellation', $reservation), [
                'reason' => 'I cannot collect the item.',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'cancellation_requested',
            'cancellation_refund_percentage' => 0,
            'cancellation_refund_total' => 0,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.reservations.cancellation-decision', $reservation), [
                'decision' => 'approve',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'cancelled',
            'cancellation_status' => 'approved',
        ]);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'paid',
            'refunded_amount' => 0,
        ]);
    }

    private function postSignedRefundEvent(array $refund): TestResponse
    {
        $payload = json_encode([
            'id' => 'evt_test_async_refund',
            'object' => 'event',
            'api_version' => '2025-04-30.basil',
            'created' => time(),
            'livemode' => false,
            'type' => 'refund.updated',
            'data' => ['object' => $refund],
        ], JSON_THROW_ON_ERROR);
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$payload, 'whsec_test_reservation_refund');

        return $this->call(
            'POST',
            route('stripe.webhook'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}",
            ],
            $payload
        );
    }
}
