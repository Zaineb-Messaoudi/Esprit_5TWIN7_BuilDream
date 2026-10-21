<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const WEBHOOK_SECRET = 'whsec_test_checkout';

    public function test_signed_webhook_records_the_deposit_without_starting_the_rental(): void
    {
        config([
            'services.stripe.webhook_secret' => self::WEBHOOK_SECRET,
            'services.stripe.checkout_currency' => 'usd',
            'services.stripe.tnd_to_checkout_rate' => 0.32,
        ]);

        $buyer = User::factory()->create([
            'role' => UserRole::BUYER,
            'role_setup_completed' => true,
        ]);
        $reservation = Reservation::factory()->create([
            'user_id' => $buyer->id,
            'start_date' => today()->addDays(20),
            'total_amount' => 100,
            'status' => 'confirmed',
        ]);
        $sessionId = 'cs_test_deposit_webhook';
        $reservation->payments()->create([
            'amount' => 35.7,
            'payment_stage' => 'deposit',
            'due_date' => today(),
            'payment_date' => now(),
            'transaction_reference' => $sessionId,
            'status' => 'pending',
            'payment_method' => 'CARD',
        ]);

        $session = [
            'id' => $sessionId,
            'object' => 'checkout.session',
            'mode' => 'payment',
            'status' => 'complete',
            'payment_status' => 'paid',
            'currency' => 'usd',
            'amount_total' => 1142,
            'metadata' => [
                'reservation_id' => (string) $reservation->id,
                'user_id' => (string) $buyer->id,
                'reservation_reference' => $reservation->reference,
            ],
        ];

        $this->postSignedStripeEvent('evt_checkout_deposit', 'checkout.session.completed', $session)
            ->assertOk()
            ->assertJson(['received' => true]);

        $this->assertDatabaseHas('payments', [
            'transaction_reference' => $sessionId,
            'status' => 'paid',
        ]);
        $this->assertDatabaseHas('invoices', [
            'reservation_id' => $reservation->id,
            'total' => 119,
            'status' => 'partially_paid',
        ]);
        $this->assertDatabaseMissing('rentals', ['reservation_id' => $reservation->id]);
        $this->assertDatabaseMissing('rental_contracts', ['rental_id' => $reservation->id]);
    }

    public function test_signed_async_checkout_webhook_completes_payment_once_and_accepts_retries(): void
    {
        config([
            'services.stripe.webhook_secret' => self::WEBHOOK_SECRET,
            'services.stripe.checkout_currency' => 'usd',
            'services.stripe.tnd_to_checkout_rate' => 0.32,
        ]);

        $buyer = User::factory()->create([
            'role' => UserRole::BUYER,
            'role_setup_completed' => true,
        ]);
        $reservation = Reservation::factory()->create([
            'user_id' => $buyer->id,
            'total_amount' => 100,
            'status' => 'confirmed',
        ]);
        $sessionId = 'cs_test_webhook_once';
        $reservation->payments()->create([
            'amount' => 119,
            'payment_date' => now(),
            'transaction_reference' => $sessionId,
            'status' => 'pending',
            'payment_method' => 'CARD',
        ]);

        $session = [
            'id' => $sessionId,
            'object' => 'checkout.session',
            'mode' => 'payment',
            'status' => 'complete',
            'payment_status' => 'unpaid',
            'currency' => 'usd',
            'amount_total' => 3808,
            'metadata' => [
                'reservation_id' => (string) $reservation->id,
                'user_id' => (string) $buyer->id,
                'reservation_reference' => $reservation->reference,
            ],
        ];

        $this->postSignedStripeEvent('evt_checkout_completed_unpaid', 'checkout.session.completed', $session)
            ->assertOk()
            ->assertJson(['received' => true]);
        $this->assertDatabaseHas('payments', [
            'transaction_reference' => $sessionId,
            'status' => 'pending',
        ]);
        $this->assertDatabaseMissing('rentals', ['reservation_id' => $reservation->id]);

        $session['payment_status'] = 'paid';
        $session['amount_total'] = 3807;
        $this->postSignedStripeEvent(
            'evt_checkout_wrong_amount',
            'checkout.session.async_payment_succeeded',
            $session
        )->assertOk();
        $this->assertDatabaseHas('payments', [
            'transaction_reference' => $sessionId,
            'status' => 'pending',
        ]);
        $this->assertDatabaseMissing('rentals', ['reservation_id' => $reservation->id]);

        $session['amount_total'] = 3808;
        $event = $this->postSignedStripeEvent(
            'evt_checkout_async_paid',
            'checkout.session.async_payment_succeeded',
            $session
        );
        $event->assertOk()->assertJson(['received' => true]);

        $this->postSignedStripeEvent(
            'evt_checkout_async_paid',
            'checkout.session.async_payment_succeeded',
            $session
        )->assertOk();

        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseHas('payments', [
            'transaction_reference' => $sessionId,
            'status' => 'paid',
        ]);
        $this->assertDatabaseCount('rentals', 1);
        $this->assertDatabaseCount('rental_contracts', 1);
        $this->assertDatabaseCount('invoices', 1);
        $this->assertDatabaseHas('stripe_webhook_events', [
            'stripe_event_id' => 'evt_checkout_async_paid',
            'status' => 'processed',
        ]);
    }

    public function test_webhook_rejects_an_invalid_signature_without_processing_the_event(): void
    {
        config(['services.stripe.webhook_secret' => self::WEBHOOK_SECRET]);

        $payload = json_encode([
            'id' => 'evt_invalid_signature',
            'object' => 'event',
            'type' => 'checkout.session.completed',
            'data' => ['object' => ['id' => 'cs_test_invalid']],
        ], JSON_THROW_ON_ERROR);

        $this->call(
            'POST',
            route('stripe.webhook'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_STRIPE_SIGNATURE' => 't='.time().',v1=invalid',
            ],
            $payload
        )->assertBadRequest();

        $this->assertDatabaseMissing('stripe_webhook_events', [
            'stripe_event_id' => 'evt_invalid_signature',
        ]);
    }

    private function postSignedStripeEvent(string $eventId, string $type, array $session): TestResponse
    {
        $payload = json_encode([
            'id' => $eventId,
            'object' => 'event',
            'api_version' => '2025-04-30.basil',
            'created' => time(),
            'livemode' => false,
            'type' => $type,
            'data' => ['object' => $session],
        ], JSON_THROW_ON_ERROR);
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$payload, self::WEBHOOK_SECRET);

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
