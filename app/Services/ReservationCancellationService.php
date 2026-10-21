<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ReservationCancellationService
{
    public function quote(Reservation $reservation): array
    {
        $daysUntilStart = today()->startOfDay()->diffInDays($reservation->start_date->copy()->startOfDay(), false);
        $percentage = match (true) {
            $daysUntilStart >= 7 => 100,
            $daysUntilStart > 0 => 50,
            default => 0,
        };
        $paidAmount = app(ReservationPaymentService::class)->amountPaid($reservation);
        $refundAmount = round($paidAmount * $percentage / 100, 2);

        return [
            'days_until_start' => $daysUntilStart,
            'percentage' => $percentage,
            'paid_amount' => $paidAmount,
            'refund_amount' => $refundAmount,
            'retained_amount' => round($paidAmount - $refundAmount, 2),
        ];
    }

    public function request(Reservation $reservation, string $reason): string
    {
        $mode = DB::transaction(function () use ($reservation, $reason): string {
            $reservation = Reservation::query()->whereKey($reservation->id)->lockForUpdate()->firstOrFail();

            if ($reservation->status === 'pending') {
                abort_if($reservation->payments()->exists(), 409, __('A reservation with payment activity cannot be withdrawn this way.'));
                $reservation->update([
                    'status' => 'cancelled',
                    'cancellation_status' => 'approved',
                    'cancellation_reason' => $reason,
                    'cancellation_requested_at' => now(),
                    'cancellation_reviewed_at' => now(),
                ]);

                return 'cancelled';
            }

            abort_unless(
                $reservation->status === 'confirmed'
                    || ($reservation->status === 'cancellation_requested' && $reservation->cancellation_status === 'requested'),
                409,
                __('This reservation cannot be cancelled in its current state.')
            );
            abort_if(
                $reservation->payments()->where('status', 'pending')->exists(),
                409,
                __('A payment is still pending. Wait for Stripe to confirm it before requesting cancellation.')
            );

            $isRetry = $reservation->status === 'cancellation_requested';
            if (! $isRetry) {
                $quote = $this->quote($reservation);
                $reservation->update([
                    'status' => 'cancellation_requested',
                    'cancellation_status' => 'requested',
                    'cancellation_reason' => $reason,
                    'cancellation_requested_at' => now(),
                    'cancellation_reviewed_by' => null,
                    'cancellation_reviewed_at' => null,
                    'cancellation_refund_percentage' => $quote['percentage'],
                    'cancellation_refund_total' => $quote['refund_amount'],
                ]);

                $paidPayments = $reservation->payments()
                    ->whereIn('status', ['paid', 'partially_refunded'])
                    ->get();
                $this->allocateRefundTargets(
                    $paidPayments,
                    $quote['refund_amount'],
                    $quote['paid_amount']
                );
            }

            $paidPayments = $reservation->payments()
                ->whereIn('status', ['paid', 'partially_refunded'])
                ->get();
            if ($paidPayments->isEmpty()) {
                $this->finish($reservation, false);

                return 'cancelled';
            }

            $refundTotal = (float) $reservation->cancellation_refund_total;
            if ($refundTotal <= 0) {
                return 'review';
            }

            $allAreStripeCardPayments = $paidPayments->every(
                fn (Payment $payment): bool => strtoupper($payment->payment_method) === 'CARD'
                    && str_starts_with($payment->transaction_reference, 'cs_')
            );

            return $allAreStripeCardPayments
                && filled(config('services.stripe.secret'))
                    ? 'automatic'
                    : 'review';
        });

        if ($mode === 'automatic') {
            $this->refundStripePayments($reservation);
            $this->finishIfRefunded($reservation);

            return $reservation->fresh()->status === 'cancelled' ? 'cancelled' : 'automatic';
        }

        return $mode;
    }

    public function approve(Reservation $reservation, User $reviewer): void
    {
        abort_unless(
            $reservation->status === 'cancellation_requested'
                && $reservation->cancellation_status === 'requested',
            409,
            __('This cancellation request has already been reviewed.')
        );

        foreach ($reservation->payments()->whereIn('status', ['paid', 'partially_refunded'])->get() as $payment) {
            if ((float) $payment->refund_target_amount <= (float) $payment->refunded_amount) {
                continue;
            }

            if (
                strtoupper($payment->payment_method) === 'CARD'
                && str_starts_with($payment->transaction_reference, 'cs_')
                && filled(config('services.stripe.secret'))
            ) {
                $this->refundStripePayment($payment);

                continue;
            }

            $payment->update([
                'refunded_amount' => $payment->refund_target_amount,
                'status' => (float) $payment->refund_target_amount >= (float) $payment->amount - 0.02
                    ? 'refunded'
                    : 'partially_refunded',
                'refund_status' => 'succeeded',
                'refunded_at' => now(),
            ]);
        }

        $reservation->update([
            'cancellation_reviewed_by' => $reviewer->id,
            'cancellation_reviewed_at' => now(),
        ]);

        $this->finishIfRefunded($reservation);
    }

    public function reject(Reservation $reservation, User $reviewer): void
    {
        abort_unless(
            $reservation->status === 'cancellation_requested'
                && $reservation->cancellation_status === 'requested',
            409,
            __('This cancellation request has already been reviewed.')
        );
        abort_if(
            $reservation->payments()
                ->where(fn ($query) => $query->where('refunded_amount', '>', 0)
                    ->orWhereIn('refund_status', ['pending', 'succeeded']))
                ->exists(),
            409,
            __('A cancellation with an initiated refund cannot be declined.')
        );

        $reservation->update([
            'status' => 'confirmed',
            'cancellation_status' => 'rejected',
            'cancellation_reviewed_by' => $reviewer->id,
            'cancellation_reviewed_at' => now(),
        ]);
    }

    public function finishIfRefunded(Reservation $reservation): void
    {
        $reservation->refresh();
        if ($reservation->status !== 'cancellation_requested') {
            return;
        }

        if ($reservation->payments()->where('status', 'pending')->exists()) {
            return;
        }

        $expectedRefund = (float) $reservation->cancellation_refund_total;
        $actualRefund = (float) $reservation->payments()->sum('refunded_amount');
        if ($actualRefund + 0.02 < $expectedRefund) {
            return;
        }

        $this->finish($reservation, $actualRefund);
    }

    private function refundStripePayments(Reservation $reservation): void
    {
        foreach ($reservation->payments()->whereIn('status', ['paid', 'partially_refunded'])->get() as $payment) {
            $this->refundStripePayment($payment);
        }
    }

    private function refundStripePayment(Payment $payment): void
    {
        $remainingToRefund = round((float) $payment->refund_target_amount - (float) $payment->refunded_amount, 2);
        if (
            $remainingToRefund <= 0
            || ($payment->refund_status === 'pending' && filled($payment->stripe_refund_id))
        ) {
            return;
        }

        $rate = (float) config('services.stripe.tnd_to_checkout_rate', 0);
        abort_if($rate <= 0, 503, __('Stripe checkout exchange rate is not configured.'));
        $amountMinor = (int) round($remainingToRefund * $rate * 100);
        if ($amountMinor <= 0) {
            throw new ConflictHttpException(__('The calculated refund is below Stripe’s minimum amount.'));
        }

        $attempt = $payment->refund_status === 'pending'
            ? max(1, (int) $payment->refund_attempts)
            : (int) $payment->refund_attempts + 1;
        $payment->update([
            'refund_status' => 'pending',
            'refund_attempts' => $attempt,
        ]);

        try {
            $refund = app(StripeConnectService::class)->createReservationRefund(
                $payment->transaction_reference,
                'reservation-'.$payment->reservation_id.'-payment-'.$payment->id.'-refund-'.$attempt,
                $payment->id,
                $amountMinor
            );
        } catch (\Stripe\Exception\ApiConnectionException $exception) {
            Log::warning('Stripe connection failed while issuing a reservation refund.', [
                'reservation_id' => $payment->reservation_id,
                'payment_id' => $payment->id,
                'exception' => $exception->getMessage(),
            ]);

            throw $exception;
        } catch (\Stripe\Exception\ApiErrorException|\RuntimeException $exception) {
            $payment->update(['refund_status' => 'failed', 'stripe_refund_id' => null]);

            Log::error('Unable to issue a Stripe refund for a reservation payment.', [
                'reservation_id' => $payment->reservation_id,
                'payment_id' => $payment->id,
                'exception' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        $paymentIntent = $refund->payment_intent;
        $paymentIntentId = is_string($paymentIntent) ? $paymentIntent : ($paymentIntent->id ?? null);
        $succeeded = $refund->status === 'succeeded';
        $actualRefundedAmount = round((int) $refund->amount / 100 / $rate, 2);
        $newRefundedAmount = min(
            (float) $payment->amount,
            round((float) $payment->refunded_amount + $actualRefundedAmount, 2)
        );
        $fullyRefunded = $newRefundedAmount >= (float) $payment->amount - 0.02;

        $payment->update([
            'stripe_payment_intent_id' => $paymentIntentId,
            'stripe_refund_id' => $refund->id,
            'refund_status' => $succeeded ? 'succeeded' : 'pending',
            'refunded_amount' => $succeeded ? $newRefundedAmount : $payment->refunded_amount,
            'status' => $succeeded ? ($fullyRefunded ? 'refunded' : 'partially_refunded') : $payment->status,
            'refunded_at' => $succeeded ? now() : null,
        ]);

        if ($refund->status === 'pending' || $refund->status === 'requires_action') {
            return;
        }

        if ($refund->status === 'failed' || $refund->status === 'canceled') {
            $payment->update([
                'stripe_refund_id' => null,
                'refund_status' => 'failed',
            ]);

            throw new ConflictHttpException(__('Stripe could not complete the refund. Please retry or contact support.'));
        }
    }

    private function finish(Reservation $reservation, float $refundedAmount): void
    {
        DB::transaction(function () use ($reservation, $refundedAmount): void {
            $locked = Reservation::query()->whereKey($reservation->id)->lockForUpdate()->first();
            if (! $locked || $locked->status === 'cancelled') {
                return;
            }

            $locked->update([
                'status' => 'cancelled',
                'cancellation_status' => 'approved',
                'cancellation_reviewed_at' => $locked->cancellation_reviewed_at ?? now(),
            ]);

            $invoice = $locked->invoice;
            if ($invoice) {
                $invoice->update([
                    'status' => match (true) {
                        $refundedAmount <= 0 => 'cancelled',
                        $refundedAmount + 0.02 >= (float) $invoice->total => 'refunded',
                        default => 'partially_refunded',
                    },
                ]);
            }
            $rental = $locked->rental;
            if ($rental && $rental->status->value !== 'completed') {
                $rental->update(['status' => 'cancelled']);
                $rental->contract?->update(['contract_status' => 'terminated']);
            }
        });
    }

    private function allocateRefundTargets($payments, float $refundTotal, float $paidTotal): void
    {
        $remainingRefund = round($refundTotal, 2);
        $remainingPaid = round($paidTotal, 2);

        foreach ($payments->values() as $index => $payment) {
            $netPaid = round((float) $payment->amount - (float) $payment->refunded_amount, 2);
            $isLastPayment = $index === $payments->count() - 1;
            $refundShare = $isLastPayment || $remainingPaid <= 0
                ? $remainingRefund
                : min($remainingRefund, round($refundTotal * $netPaid / $paidTotal, 2));
            $targetAmount = min(
                (float) $payment->amount,
                round((float) $payment->refunded_amount + $refundShare, 2)
            );

            $payment->update([
                'refund_target_amount' => $targetAmount,
                'refund_status' => $targetAmount > (float) $payment->refunded_amount ? 'pending' : 'none',
            ]);
            $remainingRefund = round($remainingRefund - $refundShare, 2);
            $remainingPaid = round($remainingPaid - $netPaid, 2);
        }
    }

    public function requiresManualRefundConfirmation(Reservation $reservation): bool
    {
        return (float) $reservation->cancellation_refund_total > 0
            && $reservation->payments()
                ->whereIn('status', ['paid', 'partially_refunded'])
                ->where(function ($query): void {
                    $query->where('payment_method', '!=', 'CARD')
                        ->orWhere('transaction_reference', 'not like', 'cs_%')
                        ->orWhereRaw('? = 1', [filled(config('services.stripe.secret')) ? 0 : 1]);
                })
                ->whereColumn('refund_target_amount', '>', 'refunded_amount')
                ->exists();
    }
}
