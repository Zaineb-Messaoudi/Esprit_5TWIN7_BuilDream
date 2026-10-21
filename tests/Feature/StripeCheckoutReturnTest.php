<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Equipment;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use App\Services\StripeConnectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeCheckoutReturnTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_success_url_includes_stripes_session_placeholder(): void
    {
        config([
            'services.stripe.secret' => 'sk_test_placeholder',
            'services.stripe.tnd_to_checkout_rate' => 0.32,
        ]);

        [$buyer, $equipment, $reservation] = $this->createConfirmedReservation();
        $successUrl = route('front.confirmed', [
            'equipment' => $equipment->id,
            'reservation' => $reservation->id,
        ]).'?checkout=success&session_id={CHECKOUT_SESSION_ID}';

        $this->mock(StripeConnectService::class)
            ->shouldReceive('createCheckoutSession')
            ->once()
            ->withArgs(function (...$arguments) use ($successUrl, $reservation, $buyer): bool {
                $this->assertSame($successUrl, $arguments[5]);
                $this->assertSame($reservation->id, $arguments[3]);
                $this->assertSame($buyer->id, $arguments[4]);

                return true;
            })
            ->andReturn(\Stripe\Checkout\Session::constructFrom([
                'id' => 'cs_test_checkout_return',
                'url' => 'https://checkout.stripe.com/test',
                'status' => 'open',
            ]));

        $this->actingAs($buyer)
            ->post(route('booking.reservations.pay', $reservation), ['payment_method' => 'CARD'])
            ->assertRedirect('https://checkout.stripe.com/test');

        $this->assertDatabaseHas('payments', [
            'reservation_id' => $reservation->id,
            'transaction_reference' => 'cs_test_checkout_return',
            'status' => 'pending',
        ]);
    }

    public function test_paid_stripe_checkout_return_verifies_payment_and_creates_rental_records(): void
    {
        config([
            'services.stripe.secret' => 'sk_test_placeholder',
            'services.stripe.checkout_currency' => 'usd',
            'services.stripe.tnd_to_checkout_rate' => 0.32,
        ]);

        [$buyer, $equipment, $reservation, $sessionId] = $this->createPendingCheckout();
        $session = $this->stripeSession($sessionId, $reservation, $buyer, 'paid');

        $this->partialMock(StripeConnectService::class)
            ->shouldReceive('retrieveCheckoutSession')
            ->once()
            ->with($sessionId)
            ->andReturn($session);

        $this->actingAs($buyer)
            ->get($this->successUrl($equipment, $reservation, $sessionId))
            ->assertOk()
            ->assertSee('Payment verified. Your rental, contract, and invoice are available in your account.')
            ->assertDontSee('Stripe is confirming your payment.');

        $this->assertDatabaseHas('payments', [
            'reservation_id' => $reservation->id,
            'transaction_reference' => $sessionId,
            'status' => 'paid',
        ]);
        $this->assertDatabaseCount('rentals', 1);
        $this->assertDatabaseCount('rental_contracts', 1);
        $this->assertDatabaseCount('invoices', 1);
    }

    public function test_unpaid_stripe_checkout_return_keeps_payment_pending(): void
    {
        config([
            'services.stripe.secret' => 'sk_test_placeholder',
            'services.stripe.checkout_currency' => 'usd',
            'services.stripe.tnd_to_checkout_rate' => 0.32,
        ]);

        [$buyer, $equipment, $reservation, $sessionId] = $this->createPendingCheckout();
        $session = $this->stripeSession($sessionId, $reservation, $buyer, 'unpaid');

        $this->partialMock(StripeConnectService::class)
            ->shouldReceive('retrieveCheckoutSession')
            ->once()
            ->with($sessionId)
            ->andReturn($session);

        $this->actingAs($buyer)
            ->get($this->successUrl($equipment, $reservation, $sessionId))
            ->assertOk()
            ->assertSee('Stripe is confirming your payment.');

        $this->assertDatabaseHas('payments', [
            'reservation_id' => $reservation->id,
            'transaction_reference' => $sessionId,
            'status' => 'pending',
        ]);
        $this->assertDatabaseMissing('rentals', ['reservation_id' => $reservation->id]);
    }

    private function createPendingCheckout(): array
    {
        [$buyer, $equipment, $reservation] = $this->createConfirmedReservation();
        $sessionId = 'cs_test_checkout_return';

        Payment::factory()->create([
            'reservation_id' => $reservation->id,
            'amount' => 119,
            'transaction_reference' => $sessionId,
            'status' => 'pending',
            'payment_method' => 'CARD',
        ]);

        return [$buyer, $equipment, $reservation, $sessionId];
    }

    private function createConfirmedReservation(): array
    {
        $buyer = User::factory()->create([
            'role' => UserRole::BUYER,
            'role_setup_completed' => true,
        ]);
        $equipment = Equipment::factory()->create();
        $reservation = Reservation::factory()->create([
            'equipment_id' => $equipment->id,
            'user_id' => $buyer->id,
            'total_amount' => 100,
            'status' => 'confirmed',
        ]);

        return [$buyer, $equipment, $reservation];
    }

    private function stripeSession(
        string $sessionId,
        Reservation $reservation,
        User $buyer,
        string $paymentStatus
    ): \Stripe\Checkout\Session {
        return \Stripe\Checkout\Session::constructFrom([
            'id' => $sessionId,
            'object' => 'checkout.session',
            'mode' => 'payment',
            'payment_status' => $paymentStatus,
            'currency' => 'usd',
            'amount_total' => 3808,
            'metadata' => [
                'reservation_id' => (string) $reservation->id,
                'user_id' => (string) $buyer->id,
                'reservation_reference' => $reservation->reference,
            ],
        ]);
    }

    private function successUrl(Equipment $equipment, Reservation $reservation, string $sessionId): string
    {
        return route('front.confirmed', [
            'equipment' => $equipment->id,
            'reservation' => $reservation->id,
            'checkout' => 'success',
            'session_id' => $sessionId,
        ]);
    }
}
