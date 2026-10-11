<?php

namespace App\Services;

use App\Models\EscrowAccount;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\StripeAccount;
use App\Models\StripeWebhookEvent;
use App\Models\User;
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
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\Exception $e) {
            Log::error('Stripe webhook signature verification failed: '.$e->getMessage());
            throw new \Exception('Invalid webhook signature');
        }

        // Log webhook event
        $webhookEvent = StripeWebhookEvent::create([
            'stripe_event_id' => $event->id,
            'type' => $event->type,
            'payload' => $event->toArray(),
            'status' => 'pending',
        ]);

        try {
            $this->processWebhookEvent($event);
            $webhookEvent->markProcessed();
        } catch (\Exception $e) {
            $webhookEvent->markFailed($e->getMessage());
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

            case 'charge.dispute.created':
                $this->handleDisputeCreated($event->data->object);
                break;
        }
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
    }

    protected function handleDisputeCreated(\Stripe\Dispute $dispute): void
    {
        $escrow = EscrowAccount::where('stripe_charge_id', $dispute->charge)->first();
        if ($escrow) {
            $escrow->update(['status' => 'disputed']);
        }
    }
}
