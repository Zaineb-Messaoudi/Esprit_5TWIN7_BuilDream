<?php

namespace App\Http\Controllers\Api;

use App\Models\EscrowAccount;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\StripeAccount;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Stripe Connect API controller for owner onboarding and escrow payments.
 */
class StripeConnectController
{
    public function __construct(private readonly \App\Services\StripeConnectService $service) {}

    /**
     * Initiate Stripe Connect onboarding for an owner.
     */
    public function onboard(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_unless($user->isOwner(), 403, 'Only equipment owners can onboard with Stripe Connect.');

        // Check if already onboarded
        $existing = StripeAccount::where('user_id', $user->id)->first();

        if ($existing && $existing->isOnboarded()) {
            return response()->json([
                'message' => 'Already onboarded with Stripe Connect.',
                'account' => $existing,
            ]);
        }

        // Create or reuse Stripe account
        $stripeAccount = $existing ?: $this->service->createConnectedAccount($user);

        // Generate onboarding link
        $refreshUrl = route('stripe.onboard.refresh');
        $returnUrl = route('stripe.onboard.return');

        $accountLink = $this->service->createAccountLink(
            $stripeAccount->stripe_account_id,
            $refreshUrl,
            $returnUrl
        );

        return response()->json([
            'message' => 'Redirect to Stripe onboarding.',
            'onboarding_url' => $accountLink->url,
            'expires_at' => $accountLink->expires_at,
        ]);
    }

    /**
     * Handle return from Stripe onboarding.
     */
    public function return(Request $request): JsonResponse
    {
        $user = $request->user();
        $stripeAccount = StripeAccount::where('user_id', $user->id)->first();

        if (! $stripeAccount) {
            return redirect()->route('profile.overview')
                ->with('error', 'Stripe account not found.');
        }

        if ($stripeAccount->isOnboarded()) {
            return redirect()->route('profile.overview')
                ->with('success', 'Stripe Connect onboarding completed successfully!');
        }

        return redirect()->route('profile.overview')
            ->with('warning', 'Onboarding incomplete. Please complete all required steps.');
    }

    /**
     * Refresh onboarding link (if expired).
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $stripeAccount = StripeAccount::where('user_id', $user->id)->first();

        abort_unless($stripeAccount, 404, 'Stripe account not found.');

        $accountLink = $this->service->createAccountLink(
            $stripeAccount->stripe_account_id,
            route('stripe.onboard.refresh'),
            route('stripe.onboard.return')
        );

        return response()->json([
            'onboarding_url' => $accountLink->url,
            'expires_at' => $accountLink->expires_at,
        ]);
    }

    /**
     * Get Stripe account status.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $stripeAccount = StripeAccount::where('user_id', $user->id)->first();

        if (! $stripeAccount) {
            return response()->json([
                'onboarded' => false,
                'message' => 'Stripe Connect not set up.',
            ]);
        }

        return response()->json([
            'onboarded' => $stripeAccount->isOnboarded(),
            'account' => [
                'stripe_account_id' => $stripeAccount->stripe_account_id,
                'status' => $stripeAccount->status,
                'charges_enabled' => $stripeAccount->charges_enabled,
                'payouts_enabled' => $stripeAccount->payouts_enabled,
                'details_submitted' => $stripeAccount->details_submitted,
                'onboarding_status' => $stripeAccount->getOnboardingStatus(),
            ],
        ]);
    }

    /**
     * Create escrow payment for a rental.
     */
    public function createEscrow(Request $request, Rental $rental): JsonResponse
    {
        $user = $request->user();

        // Verify user is the renter
        abort_unless($rental->user_id === $user->id, 403, 'Only the renter can initiate payment.');

        // Check rental status
        abort_unless($rental->status === 'confirmed', 409, 'Rental must be confirmed to proceed with payment.');

        // Check if payment already exists
        $existing = EscrowAccount::where('rental_id', $rental->id)->first();
        abort_if($existing, 409, 'Payment already exists for this rental.');

        // Get owner's Stripe account
        $ownerStripe = StripeAccount::where('user_id', $rental->equipment->owner_id)
            ->where('status', 'active')
            ->where('charges_enabled', true)
            ->first();

        abort_unless($ownerStripe, 409, 'Equipment owner has not completed Stripe onboarding.');

        // Calculate amount with platform fee (10%)
        $amount = (float) $rental->total_amount;
        $platformFee = round($amount * 0.1, 2);
        $totalAmount = $amount + $platformFee;

        // Create PaymentIntent for escrow
        $paymentIntent = $this->service->createEscrowPaymentIntent(
            $totalAmount,
            'TND',
            $ownerStripe->stripe_account_id,
            [
                'rental_id' => $rental->id,
                'stripe_account_id' => $ownerStripe->stripe_account_id,
                'renter_id' => $user->id,
                'owner_id' => $rental->equipment->owner_id,
            ]
        );

        // Create escrow record
        $escrow = EscrowAccount::create([
            'rental_id' => $rental->id,
            'payer_id' => $user->id,
            'payee_id' => $rental->equipment->owner_id,
            'stripe_payment_intent_id' => $paymentIntent->id,
            'amount' => $totalAmount,
            'platform_fee' => $platformFee,
            'stripe_fee' => 0, // Will be filled by Stripe
            'currency' => 'TND',
            'status' => 'pending',
            'metadata' => [
                'payment_intent_id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret,
            ],
        ]);

        // Update rental payment record
        Payment::create([
            'reservation_id' => $rental->reservation_id,
            'amount' => $totalAmount,
            'payment_method' => 'card',
            'transaction_reference' => 'ESC-'.strtoupper(\Illuminate\Support\Str::random(10)),
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Escrow payment initiated.',
            'client_secret' => $paymentIntent->client_secret,
            'escrow' => $escrow->load(['payer', 'payee']),
        ], 201);
    }

    /**
     * Confirm escrow payment (after client-side confirmation).
     */
    public function confirmEscrow(Request $request, EscrowAccount $escrow): JsonResponse
    {
        $user = $request->user();

        abort_unless($escrow->payer_id === $user->id, 403, 'Only payer can confirm escrow.');

        $validated = $request->validate([
            'payment_method_id' => ['required', 'string'],
        ]);

        try {
            // Confirm the PaymentIntent
            $paymentIntent = \Stripe\PaymentIntent::retrieve($escrow->stripe_payment_intent_id, [
                'stripe_account' => $escrow->payee->stripeAccount->stripe_account_id,
            ]);

            // Update with payment method
            $paymentIntent->payment_method = $validated['payment_method_id'];
            $paymentIntent->confirm();

            if ($paymentIntent->status === 'succeeded') {
                $escrow->update([
                    'status' => 'captured',
                    'stripe_charge_id' => $paymentIntent->charges->data[0]->id,
                    'captured_at' => now(),
                ]);

                // Update payment record
                $payment = Payment::where('reservation_id', $escrow->rental->reservation_id)->first();
                if ($payment) {
                    $payment->update([
                        'status' => 'paid',
                        'payment_date' => now(),
                    ]);
                }

                // Update rental status
                $escrow->rental->update(['status' => 'active']);

                return response()->json([
                    'message' => 'Payment confirmed. Funds held in escrow.',
                    'escrow' => $escrow->load(['payer', 'payee']),
                ]);
            } else {
                return response()->json([
                    'message' => 'Payment requires additional action.',
                    'status' => $paymentIntent->status,
                ], 400);
            }
        } catch (\Exception $e) {
            \Log::error("Escrow confirmation failed: {$e->getMessage()}");

            return response()->json([
                'message' => 'Payment confirmation failed.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Release escrow (admin or owner action).
     */
    public function release(Request $request, EscrowAccount $escrow): JsonResponse
    {
        $user = $request->user();

        // Only admin or owner can release
        abort_unless(
            $user->can('admin-only') || $escrow->payee_id === $user->id,
            403
        );

        abort_unless($escrow->canBeReleased(), 409, 'Escrow cannot be released in current state.');

        $this->service->releaseEscrow($escrow);

        return response()->json([
            'message' => 'Escrow released successfully.',
            'escrow' => $escrow->fresh()->load(['payer', 'payee']),
        ]);
    }

    /**
     * Refund escrow (admin or renter action).
     */
    public function refund(Request $request, EscrowAccount $escrow): JsonResponse
    {
        $user = $request->user();

        // Admin or payer can refund
        abort_unless(
            $user->can('admin-only') || $escrow->payer_id === $user->id,
            403
        );

        abort_unless($escrow->canBeRefunded(), 409, 'Escrow cannot be refunded in current state.');

        $amount = $request->input('amount');

        $this->service->refundEscrow($escrow, $amount);

        return response()->json([
            'message' => 'Escrow refunded successfully.',
            'escrow' => $escrow->fresh()->load(['payer', 'payee']),
        ]);
    }

    /**
     * Get escrow details.
     */
    public function show(EscrowAccount $escrow): JsonResponse
    {
        $user = request()->user();

        abort_unless(
            in_array($user->id, [$escrow->payer_id, $escrow->payee_id]) || $user->can('admin-only'),
            403
        );

        return response()->json($escrow->load(['payer:id,name,email', 'payee:id,name,email', 'rental.equipment']));
    }
}
