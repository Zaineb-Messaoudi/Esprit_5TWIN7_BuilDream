<?php

namespace App\Services;

use App\Models\EscrowAccount;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\StripeAccount;
use App\Models\StripeWebhookEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;

class StripeConnectService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a Stripe Connect account for an owner.
     */
    public function createConnectedAccount(User $user): \Stripe\Account
    {
        $account = \Stripe\Account::create([
            'type' => 'express',
            'email' => $user->email,
            'country' => 'TN', // Tunisia
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
            ],
            'business_type' => 'individual',
            'business_profile' => [
                'name' => $user->name."'s SolarShare Equipment",
                'url' => config('app.url'),
            ],
            'settings' => [
                'payouts' => [
                    'schedule' => [
                        'interval' => 'daily',
                    ],
                ],
            ],
            'metadata' => [
                'solarshare_user_id' => $user->id,
            ],
        ]);

        StripeAccount::create([
            'user_id' => $user->id,
            'stripe_account_id' => $account->id,
            'status' => $account->status,
            'charges_enabled' => $account->charges_enabled ?? false,
            'payouts_enabled' => $account->payouts_enabled ?? false,
            'details_submitted' => $account->details_submitted ?? false,
            'requirements' => $account->requirements->toArray() ?? [],
            'capabilities' => $account->capabilities->toArray() ?? [],
            'business_type' => 'individual',
            'country' => 'TN',
            'default_currency' => 'tnd',
        ]);

        return $account;
    }

    /**
     * Generate onboarding link for Express account.
     */
    public function createAccountLink(string $stripeAccountId, string $refreshUrl, string $returnUrl): \Stripe\AccountLink
    {
        return \Stripe\AccountLink::create([
            'account' => $stripeAccountId,
            'refresh_url' => $refreshUrl,
            'return_url' => $returnUrl,
            'type' => 'account_onboarding',
        ]);
    }

    /**
     * Create a hosted Stripe Checkout session for a real reservation payment.
     */
    public function createCheckoutSession(
        float $amount,
        string $currency,
        string $reservationReference,
        int $reservationId,
        int $userId,
        string $successUrl,
        string $cancelUrl
    ): \Stripe\Checkout\Session {
        return \Stripe\Checkout\Session::create([
            'mode' => 'payment',
            'managed_payments' => ['enabled' => false],
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower($currency),
                    'unit_amount' => (int) round($amount * 100),
                    'product_data' => [
                        'name' => 'Reservation '.$reservationReference,
                    ],
                ],
                'quantity' => 1,
            ]],
            'metadata' => [
                'reservation_id' => $reservationId,
                'user_id' => $userId,
                'reservation_reference' => $reservationReference,
            ],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);
    }

    public function retrieveCheckoutSession(string $sessionId): \Stripe\Checkout\Session
    {
        return \Stripe\Checkout\Session::retrieve($sessionId);
    }

    public function createReservationRefund(
        string $sessionId,
        string $idempotencyKey,
        int $paymentId,
        ?int $amountMinor = null
    ): \Stripe\Refund {
        $session = $this->retrieveCheckoutSession($sessionId);
        $paymentIntent = $session->payment_intent;
        $paymentIntentId = is_string($paymentIntent) ? $paymentIntent : ($paymentIntent->id ?? null);

        if (! $paymentIntentId) {
            throw new \RuntimeException('The Stripe Checkout Session has no PaymentIntent to refund.');
        }

        $refundData = ['payment_intent' => $paymentIntentId];
        $refundData['metadata'] = [
            'reservation_id' => (string) ($session->metadata['reservation_id'] ?? ''),
            'payment_id' => (string) $paymentId,
        ];
        if ($amountMinor !== null) {
            if ($amountMinor < 1 || $amountMinor > (int) $session->amount_total) {
                throw new \InvalidArgumentException('The requested refund amount is outside the paid Checkout amount.');
            }
            $refundData['amount'] = $amountMinor;
        }

        return \Stripe\Refund::create(
            $refundData,
            ['idempotency_key' => $idempotencyKey]
        );
    }

    /**
     * Create a PaymentIntent for escrow (hold funds).
     */
    public function createEscrowPaymentIntent(
        float $amount,
        string $currency,
        string $stripeAccountId,
        array $metadata = []
    ): \Stripe\PaymentIntent {
        return \Stripe\PaymentIntent::create([
            'amount' => (int) round($amount * 100), // Convert to cents
            'currency' => strtolower($currency),
            'payment_method_types' => ['card'],
            'capture_method' => 'manual', // Hold funds (escrow)
            'application_fee_amount' => (int) round($amount * 0.1 * 100), // 10% platform fee
            'transfer_data' => [
                'destination' => $metadata['stripe_account_id'] ?? '',
            ],
            'metadata' => array_merge($metadata, [
                'platform' => 'solarshare',
            ]),
        ], [
            'stripe_account' => $stripeAccountId,
        ]);
    }

    /**
     * Capture a PaymentIntent (confirm payment, move to escrow).
     */
    public function capturePaymentIntent(string $paymentIntentId, string $stripeAccountId): \Stripe\PaymentIntent
    {
        return \Stripe\PaymentIntent::capture($paymentIntentId, [], [
            'stripe_account' => $stripeAccountId,
        ]);
    }

    /**
     * Release escrow funds to connected account (owner).
     */
    public function releaseEscrow(EscrowAccount $escrow): void
    {
        if (! $escrow->canBeReleased()) {
            throw new \Exception('Escrow cannot be released in current state: '.$escrow->status);
        }

        try {
            // Create transfer to connected account
            $transfer = \Stripe\Transfer::create([
                'amount' => (int) round($escrow->net_amount * 100),
                'currency' => strtolower($escrow->currency),
                'destination' => $escrow->payee->stripeAccount->stripe_account_id,
                'source_transaction' => $escrow->stripe_charge_id,
                'metadata' => [
                    'escrow_id' => $escrow->id,
                    'rental_id' => $escrow->rental_id,
                    'platform' => 'solarshare',
                ],
            ]);

            $escrow->update([
                'status' => 'released',
                'stripe_transfer_id' => $transfer->id,
                'released_at' => now(),
            ]);

            // Update rental status if needed
            $escrow->rental->update(['status' => 'active']);

            Log::info("Escrow released: {$escrow->id}, transfer: {$transfer->id}");
        } catch (\Exception $e) {
            Log::error("Failed to release escrow {$escrow->id}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Refund escrow to payer (renter).
     */
    public function refundEscrow(EscrowAccount $escrow, ?float $amount = null): void
    {
        if (! $escrow->canBeRefunded()) {
            throw new \Exception('Escrow cannot be refunded in current state: '.$escrow->status);
        }

        $refundAmount = $amount ?? $escrow->amount;

        try {
            $refund = \Stripe\Refund::create([
                'charge' => $escrow->stripe_charge_id,
                'amount' => (int) round($refundAmount * 100),
                'reason' => 'requested_by_customer',
                'metadata' => [
                    'escrow_id' => $escrow->id,
                    'rental_id' => $escrow->rental_id,
                ],
            ]);

            $escrow->update([
                'status' => 'refunded',
                'stripe_refund_id' => $refund->id,
                'refunded_at' => now(),
            ]);

            Log::info("Escrow refunded: {$escrow->id}, refund: {$refund->id}");
        } catch (\Exception $e) {
            Log::error("Failed to refund escrow {$escrow->id}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Handle Stripe webhook event.
     */
    public function handleWebhook(string $payload, string $sigHeader): void
    {
        $endpointSecret = config('services.stripe.webhook_secret');
        if (! filled($endpointSecret)) {
            throw new \RuntimeException('Stripe webhook secret is not configured.');
        }

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\Stripe\Exception\SignatureVerificationException|\Stripe\Exception\UnexpectedValueException $e) {
            Log::warning('Stripe webhook signature or payload validation failed.', [
                'exception' => $e->getMessage(),
            ]);
            throw new \InvalidArgumentException('Invalid Stripe webhook signature or payload.', 0, $e);
        }

        DB::table('stripe_webhook_events')->insertOrIgnore([
            'stripe_event_id' => $event->id,
            'type' => $event->type,
            'payload' => json_encode($event->toArray(), JSON_THROW_ON_ERROR),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            DB::transaction(function () use ($event): void {
                $webhookEvent = StripeWebhookEvent::where('stripe_event_id', $event->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($webhookEvent->isProcessed()) {
                    return;
                }

                $webhookEvent->update([
                    'status' => 'pending',
                    'error_message' => null,
                ]);

                $this->processWebhookEvent($event);
                $webhookEvent->markProcessed();
            });
        } catch (\Throwable $e) {
            StripeWebhookEvent::where('stripe_event_id', $event->id)->update([
                'status' => 'failed',
                'error_message' => mb_substr($e->getMessage(), 0, 65535),
            ]);

            Log::error('Stripe webhook event processing failed.', [
                'event_id' => $event->id,
                'type' => $event->type,
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Process specific webhook events.
     */
    protected function processWebhookEvent(\Stripe\Event $event): void
    {
        switch ($event->type) {
            case 'account.updated':
                $this->handleAccountUpdated($event->data->object);
                break;

            case 'checkout.session.completed':
            case 'checkout.session.async_payment_succeeded':
                $this->handleCheckoutSessionCompleted(
                    \Stripe\Checkout\Session::constructFrom($event->data->object->toArray())
                );
                break;

            case 'checkout.session.async_payment_failed':
            case 'checkout.session.expired':
                $this->handleCheckoutSessionFailed(
                    \Stripe\Checkout\Session::constructFrom($event->data->object->toArray())
                );
                break;

            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;

            case 'payment_intent.payment_failed':
                $this->handlePaymentFailed($event->data->object);
                break;

            case 'transfer.created':
                $this->handleTransferCreated($event->data->object);
                break;

            case 'transfer.failed':
                $this->handleTransferFailed($event->data->object);
                break;

            case 'charge.refunded':
                $this->handleChargeRefunded($event->data->object);
                break;

            case 'refund.updated':
                $this->handleRefundUpdated(\Stripe\Refund::constructFrom($event->data->object->toArray()));
                break;

            case 'charge.dispute.created':
                $this->handleDisputeCreated($event->data->object);
                break;
        }
    }

    protected function handleCheckoutSessionCompleted(\Stripe\Checkout\Session $session): void
    {
        if ($session->payment_status !== 'paid') {
            return;
        }

        $reservationId = $session->metadata['reservation_id'] ?? null;
        $userId = $session->metadata['user_id'] ?? null;
        if (! $reservationId || ! $userId) {
            return;
        }

        $reservation = Reservation::find($reservationId);
        if ($reservation) {
            $paymentExists = Payment::where('transaction_reference', $session->id)
                ->where('reservation_id', $reservation->id)
                ->exists();

            if (! $paymentExists) {
                throw new \RuntimeException('No local payment attempt matches the completed Stripe Checkout Session.');
            }

            if (! $this->completeCheckoutSession($session, $reservation, (int) $userId)) {
                Log::warning('Stripe Checkout Session did not match the reservation payment details.', [
                    'reservation_id' => $reservation->id,
                    'session_id' => $session->id,
                ]);
            }
        }
    }

    protected function handleCheckoutSessionFailed(\Stripe\Checkout\Session $session): void
    {
        Payment::where('transaction_reference', $session->id)
            ->where('status', 'pending')
            ->update(['status' => 'failed']);
    }

    public function completeCheckoutSession(
        \Stripe\Checkout\Session $session,
        Reservation $reservation,
        int $userId
    ): bool {
        $payment = Payment::where('transaction_reference', $session->id)
            ->where('reservation_id', $reservation->id)
            ->first();
        if (! $payment) {
            return false;
        }

        $expectedAmount = round((float) $payment->amount, 2);
        $checkoutCurrency = strtolower((string) config('services.stripe.checkout_currency', 'usd'));
        $conversionRate = (float) config('services.stripe.tnd_to_checkout_rate', 0);
        $expectedCheckoutAmount = round($expectedAmount * $conversionRate, 2);
        $expectedAmountInMinorUnits = (int) round($expectedCheckoutAmount * 100);

        if (
            $session->mode !== 'payment'
            || $session->payment_status !== 'paid'
            || (int) ($session->metadata['reservation_id'] ?? 0) !== (int) $reservation->id
            || (int) ($session->metadata['user_id'] ?? 0) !== $userId
            || (int) $reservation->user_id !== $userId
            || $reservation->status !== 'confirmed'
            || $conversionRate <= 0
            || strtolower((string) $session->currency) !== $checkoutCurrency
            || (int) $session->amount_total !== $expectedAmountInMinorUnits
        ) {
            return false;
        }

        return DB::transaction(function () use ($session, $reservation, $expectedAmount): bool {
            $lockedReservation = Reservation::whereKey($reservation->id)->lockForUpdate()->first();
            if (! $lockedReservation || $lockedReservation->status !== 'confirmed') {
                return false;
            }

            $payment = Payment::where('transaction_reference', $session->id)
                ->lockForUpdate()
                ->first();

            if (
                ! $payment
                || (int) $payment->reservation_id !== (int) $lockedReservation->id
                || round((float) $payment->amount, 2) !== $expectedAmount
                || $payment->status === 'failed'
            ) {
                return false;
            }

            if ($payment->status !== 'paid') {
                $payment->update([
                    'status' => 'paid',
                    'payment_method' => 'CARD',
                    'payment_date' => now(),
                ]);
            }

            app(ReservationPaymentService::class)->synchronizeAfterPayment($lockedReservation);

            return true;
        });
    }

    protected function handleAccountUpdated(\Stripe\Account $account): void
    {
        StripeAccount::where('stripe_account_id', $account->id)->update([
            'status' => $account->status,
            'charges_enabled' => $account->charges_enabled ?? false,
            'payouts_enabled' => $account->payouts_enabled ?? false,
            'details_submitted' => $account->details_submitted ?? false,
            'requirements' => $account->requirements->toArray() ?? [],
            'capabilities' => $account->capabilities->toArray() ?? [],
            'onboarded_at' => $account->charges_enabled && $account->payouts_enabled ? now() : null,
        ]);
    }

    protected function handlePaymentIntentSucceeded(\Stripe\PaymentIntent $pi): void
    {
        $escrow = EscrowAccount::where('stripe_payment_intent_id', $pi->id)->first();
        if ($escrow && $pi->status === 'succeeded') {
            $escrow->update([
                'status' => 'captured',
                'stripe_charge_id' => $pi->charges->data[0]->id ?? null,
                'captured_at' => now(),
            ]);
        }
    }

    protected function handlePaymentFailed(\Stripe\PaymentIntent $pi): void
    {
        $escrow = EscrowAccount::where('stripe_payment_intent_id', $pi->id)->first();
        if ($escrow) {
            $escrow->update(['status' => 'cancelled']);
        }
    }

    protected function handleTransferCreated(\Stripe\Transfer $transfer): void
    {
        $escrow = EscrowAccount::where('stripe_transfer_id', $transfer->id)->first();
        if ($escrow && $transfer->status === 'paid') {
            $escrow->update([
                'status' => 'released',
                'released_at' => now(),
            ]);
        }
    }

    protected function handleTransferFailed(\Stripe\Transfer $transfer): void
    {
        $escrow = EscrowAccount::where('stripe_transfer_id', $transfer->id)->first();
        if ($escrow) {
            $escrow->update(['status' => 'disputed']);
        }
    }

    protected function handleChargeRefunded(\Stripe\Charge $charge): void
    {
        $escrow = EscrowAccount::where('stripe_charge_id', $charge->id)->first();
        if ($escrow) {
            $escrow->update([
                'status' => 'refunded',
                'refunded_at' => now(),
                'stripe_refund_id' => $charge->refunds->data[0]->id ?? null,
            ]);
        }

        $paymentIntentId = is_string($charge->payment_intent)
            ? $charge->payment_intent
            : ($charge->payment_intent->id ?? null);
        $payment = $paymentIntentId
            ? Payment::where('stripe_payment_intent_id', $paymentIntentId)->first()
            : null;

        if ($payment && $charge->refunded) {
            $this->recordStripeRefund(
                $payment,
                (int) $charge->amount_refunded,
                $charge->refunds->data[0]->id ?? null,
                true
            );
            app(ReservationCancellationService::class)->finishIfRefunded($payment->reservation);
        }
    }

    protected function handleRefundUpdated(\Stripe\Refund $refund): void
    {
        $paymentIntentId = is_string($refund->payment_intent)
            ? $refund->payment_intent
            : ($refund->payment_intent->id ?? null);
        $paymentId = $refund->metadata['payment_id'] ?? null;
        $payment = $paymentId
            ? Payment::find($paymentId)
            : ($paymentIntentId
                ? Payment::where('stripe_payment_intent_id', $paymentIntentId)->first()
                : null);

        if (! $payment) {
            return;
        }

        if ($refund->status === 'succeeded') {
            $this->recordStripeRefund($payment, (int) $refund->amount, $refund->id, true);
            app(ReservationCancellationService::class)->finishIfRefunded($payment->reservation);
        } elseif ($refund->status === 'pending' || $refund->status === 'requires_action') {
            $this->recordStripeRefund($payment, (int) $refund->amount, $refund->id, false);
        } elseif ($refund->status === 'failed' || $refund->status === 'canceled') {
            $payment->update([
                'refund_status' => 'failed',
                'stripe_refund_id' => null,
            ]);
        }
    }

    private function recordStripeRefund(Payment $payment, int $amountMinor, ?string $refundId, bool $succeeded): void
    {
        $rate = (float) config('services.stripe.tnd_to_checkout_rate', 0);
        if ($rate <= 0) {
            throw new \RuntimeException('Stripe refund exchange rate is not configured.');
        }

        $refundedAmount = round($amountMinor / 100 / $rate, 2);
        $sameRefund = $refundId && $payment->stripe_refund_id === $refundId;
        $newTotalRefunded = ! $succeeded
            ? (float) $payment->refunded_amount
            : ($sameRefund
                ? max((float) $payment->refunded_amount, $refundedAmount)
                : min((float) $payment->amount, round((float) $payment->refunded_amount + $refundedAmount, 2)));
        $fullyRefunded = $newTotalRefunded >= (float) $payment->amount - 0.02;

        $payment->update([
            'refunded_amount' => min((float) $payment->amount, $newTotalRefunded),
            'status' => $succeeded
                ? ($fullyRefunded ? 'refunded' : 'partially_refunded')
                : $payment->status,
            'refund_status' => $succeeded ? 'succeeded' : 'pending',
            'stripe_payment_intent_id' => $paymentIntentId ?? $payment->stripe_payment_intent_id,
            'stripe_refund_id' => $refundId ?? $payment->stripe_refund_id,
            'refunded_at' => $succeeded ? now() : null,
        ]);
    }

    protected function handleDisputeCreated(\Stripe\Dispute $dispute): void
    {
        $escrow = EscrowAccount::where('stripe_charge_id', $dispute->charge)->first();
        if ($escrow) {
            $escrow->update(['status' => 'disputed']);
        }
    }
}
