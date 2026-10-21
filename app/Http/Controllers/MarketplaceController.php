<?php

namespace App\Http\Controllers;

use App\Events\ReservationApproved;
use App\Events\ReservationCreated;
use App\Events\ReservationRejected;
use App\Models\Equipment;
use App\Models\RentalContract;
use App\Models\Reservation;
use App\Services\ReservationAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/** Live renter and owner actions for the equipment booking lifecycle. */
class MarketplaceController extends Controller
{
    public function storeReservation(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isBuyer(), 403);
        $data = $request->validate([
            'equipment_id' => ['required', 'integer', 'exists:equipment,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $start = $data['start_date'];
        $end = $data['end_date'];
        $days = max(1, Carbon::parse($start)->startOfDay()->diffInDays(Carbon::parse($end)->startOfDay()) + 1);
        $reservation = DB::transaction(function () use ($data, $start, $end, $days, $request): Reservation {
            // Serialize availability checks for this listing so concurrent bookings cannot both pass.
            $equipment = Equipment::query()
                ->where('approval_status', 'published')
                ->where('status', 'available')
                ->lockForUpdate()
                ->findOrFail($data['equipment_id']);

            app(ReservationAvailabilityService::class)->assertAvailable($equipment->id, $start, $end);

            return Reservation::create([
                'reference' => 'RES-'.strtoupper(Str::random(8)),
                'equipment_id' => $equipment->id,
                'user_id' => $request->user()->id,
                'start_date' => $start,
                'end_date' => $end,
                'total_amount' => round((float) $equipment->price_per_day * $days, 2),
                'status' => 'pending',
            ]);
        });
        $equipment = $reservation->equipment()->with('owner')->firstOrFail();

        // Fire event for real-time notification to owner
        ReservationCreated::dispatch($reservation->load('equipment', 'user'), $equipment->owner);

        return redirect()->route('front.booking-summary', [
            'equipment' => $equipment->id,
            'reservation' => $reservation->id,
        ])->with('status', __('Reservation request sent to the equipment owner.'));
    }

    public function decideReservation(Request $request, Reservation $reservation): RedirectResponse
    {
        abort_unless((int) $reservation->equipment()->value('owner_id') === (int) $request->user()->id, 403);
        $data = $request->validate(['decision' => ['required', 'in:approve,reject']]);
        abort_unless($reservation->status === 'pending', 409);

        $reservation->update(['status' => $data['decision'] === 'approve' ? 'confirmed' : 'cancelled']);

        // Fire real-time notification events
        if ($data['decision'] === 'approve') {
            ReservationApproved::dispatch($reservation->load('equipment'), $reservation->user);
        } else {
            ReservationRejected::dispatch($reservation->load('equipment'), $reservation->user);
        }

        return back()->with('status', $data['decision'] === 'approve'
            ? __('Reservation approved. The renter can now record payment.')
            : __('Reservation declined.'));
    }

    public function pay(Request $request, Reservation $reservation): RedirectResponse
    {
        abort_unless((int) $reservation->user_id === (int) $request->user()->id, 403);
        abort_unless($reservation->status === 'confirmed', 409);
        $data = $request->validate(['payment_method' => ['required', 'in:CARD,BANK_TRANSFER,CASH']]);
        $paymentPlan = app(\App\Services\ReservationPaymentService::class);
        $amount = $paymentPlan->nextPaymentAmount($reservation);
        abort_if($amount <= 0, 409, __('This reservation is already fully paid.'));
        $paymentStage = $paymentPlan->nextPaymentStage($reservation);
        $dueDate = $paymentPlan->nextPaymentDueDate($reservation);

        if ($data['payment_method'] === 'CARD' && filled(config('services.stripe.secret'))) {
            $stripeCurrency = strtolower(config('services.stripe.checkout_currency', 'usd'));
            $tndToStripeRate = (float) config('services.stripe.tnd_to_checkout_rate', 0);

            abort_if($tndToStripeRate <= 0, 503, __('Stripe checkout exchange rate is not configured.'));

            try {
                $stripe = app(\App\Services\StripeConnectService::class);
                $existingStripePayment = $reservation->payments()
                    ->where('status', 'pending')
                    ->where('transaction_reference', 'like', 'cs_%')
                    ->latest()
                    ->first();

                if ($existingStripePayment) {
                    $existingSession = $stripe->retrieveCheckoutSession($existingStripePayment->transaction_reference);

                    if ($existingSession->status === 'open' && $existingSession->url) {
                        return redirect()->away($existingSession->url);
                    }

                    if ($existingSession->status === 'complete') {
                        return redirect()->route('front.confirmed', [
                            'equipment' => $reservation->equipment_id,
                            'reservation' => $reservation->id,
                            'checkout' => 'success',
                            'session_id' => $existingStripePayment->transaction_reference,
                        ]);
                    }

                    $existingStripePayment->update(['status' => 'failed']);
                }

                $session = $stripe->createCheckoutSession(
                    round($amount * $tndToStripeRate, 2),
                    $stripeCurrency,
                    $reservation->reference,
                    $reservation->id,
                    $request->user()->id,
                    route('front.confirmed', ['equipment' => $reservation->equipment_id, 'reservation' => $reservation->id]).'?checkout=success&session_id={CHECKOUT_SESSION_ID}',
                    route('front.booking-summary', ['equipment' => $reservation->equipment_id, 'reservation' => $reservation->id]).'?checkout=cancelled'
                );

                DB::transaction(function () use ($reservation, $amount, $paymentStage, $dueDate, $session): void {
                    $reservation->payments()
                        ->where('status', 'pending')
                        ->update(['status' => 'failed']);

                    $reservation->payments()->create([
                        'amount' => $amount,
                        'payment_stage' => $paymentStage,
                        'due_date' => $dueDate,
                        'payment_date' => now(),
                        'transaction_reference' => $session->id,
                        'status' => 'pending',
                        'payment_method' => 'card',
                    ]);
                });

                return redirect()->away($session->url);
            } catch (\Stripe\Exception\ApiConnectionException $exception) {
                Log::warning('Unable to connect to Stripe Checkout.', [
                    'reservation_id' => $reservation->id,
                    'exception' => $exception->getMessage(),
                ]);

                return back()->withErrors([
                    'payment' => __('Could not connect to Stripe. Check your internet connection and try again.'),
                ]);
            } catch (\Stripe\Exception\ApiErrorException $exception) {
                Log::error('Stripe rejected a Checkout request.', [
                    'reservation_id' => $reservation->id,
                    'exception' => $exception->getMessage(),
                ]);

                return back()->withErrors([
                    'payment' => __('Stripe could not start the payment. Please try again or contact support.'),
                ]);
            }
        }

        abort_if(
            $data['payment_method'] === 'CARD',
            503,
            __('Card payments are unavailable because Stripe is not configured.')
        );

        DB::transaction(function () use ($reservation, $data, $amount, $paymentStage, $dueDate): void {
            $existing = $reservation->payments()->where('status', 'pending')->latest()->first();
            if ($existing) {
                $existing->update(['payment_method' => $data['payment_method']]);

                return;
            }

            $reservation->payments()->create([
                'amount' => $amount,
                'payment_stage' => $paymentStage,
                'due_date' => $dueDate,
                'payment_date' => now(),
                'transaction_reference' => 'PAY-'.strtoupper(Str::random(10)),
                'status' => 'pending',
                'payment_method' => $data['payment_method'],
            ]);
        });

        return redirect()->route('front.confirmed', [
            'equipment' => $reservation->equipment_id,
            'reservation' => $reservation->id,
        ])->with('status', __('Payment method recorded. Payment is awaiting back-office verification.'));
    }

    /**
     * CHANGED: a contract can no longer be signed in one click.
     * Teacher's rule: the client must READ the contract before signing it.
     * The old "Sign contract" buttons still post here, so instead of signing we send the
     * renter to the contract page, where he reads it, ticks "I have read and accept",
     * and signs (Front\ContractController@accept).
     */
    public function signContract(Request $request, RentalContract $contract): RedirectResponse
    {
        $contract->loadMissing('rental');
        abort_unless((int) $contract->rental->user_id === (int) $request->user()->id, 403);

        return redirect()
            ->route('contracts.show', $contract)
            ->with('status', 'contract-read-first');
    }
}
