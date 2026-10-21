<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Services\ReservationCancellationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ReservationCancellationController extends Controller
{
    public function request(Request $request, Reservation $reservation, ReservationCancellationService $cancellations): RedirectResponse
    {
        abort_unless($request->user()->isBuyer() && (int) $reservation->user_id === (int) $request->user()->id, 403);

        $reasonRules = $reservation->cancellation_status === 'requested'
            ? ['nullable', 'string', 'min:5', 'max:1000']
            : ['required', 'string', 'min:5', 'max:1000'];
        $data = $request->validate(['reason' => $reasonRules]);

        try {
            $result = $cancellations->request($reservation, $data['reason'] ?? $reservation->cancellation_reason ?? '');
        } catch (\Stripe\Exception\ApiConnectionException $exception) {
            Log::warning('Stripe connection failed while refunding a reservation.', [
                'reservation_id' => $reservation->id,
                'exception' => $exception->getMessage(),
            ]);

            return back()->withErrors(['cancellation' => __('The refund service is temporarily unavailable. Your request is saved; please retry shortly.')]);
        } catch (\Stripe\Exception\ApiErrorException $exception) {
            Log::error('Stripe rejected a reservation refund.', [
                'reservation_id' => $reservation->id,
                'exception' => $exception->getMessage(),
            ]);

            return back()->withErrors(['cancellation' => __('Stripe could not complete the refund. Your request is saved for follow-up.')]);
        } catch (ConflictHttpException $exception) {
            return back()->withErrors(['cancellation' => $exception->getMessage()]);
        }

        return back()->with('status', match ($result) {
            'cancelled' => __('Reservation cancelled.'),
            'automatic' => __('Cancellation accepted. Stripe is processing the refund; this page will update when it is confirmed.'),
            default => __('Cancellation request sent for administrator review.'),
        });
    }

    public function decide(Request $request, Reservation $reservation, ReservationCancellationService $cancellations): RedirectResponse
    {
        $rules = [
            'decision' => ['required', 'in:approve,reject'],
        ];
        if ($request->input('decision') === 'approve' && $cancellations->requiresManualRefundConfirmation($reservation)) {
            $rules['manual_refund_confirmed'] = ['required', 'accepted'];
        }
        $data = $request->validate($rules);

        try {
            if ($data['decision'] === 'approve') {
                $cancellations->approve($reservation, $request->user());

                return back()->with('status', __('Cancellation approved. Refunds are recorded or processing.'));
            }

            $cancellations->reject($reservation, $request->user());

            return back()->with('status', __('Cancellation request declined. The reservation remains confirmed.'));
        } catch (\Stripe\Exception\ApiConnectionException $exception) {
            Log::warning('Stripe connection failed during cancellation review.', [
                'reservation_id' => $reservation->id,
                'exception' => $exception->getMessage(),
            ]);

            return back()->withErrors(['cancellation' => __('The refund service is temporarily unavailable. Retry the review shortly.')]);
        } catch (\Stripe\Exception\ApiErrorException $exception) {
            Log::error('Stripe rejected a refund during cancellation review.', [
                'reservation_id' => $reservation->id,
                'exception' => $exception->getMessage(),
            ]);

            return back()->withErrors(['cancellation' => __('Stripe could not complete the refund. The cancellation remains under review.')]);
        } catch (ConflictHttpException $exception) {
            return back()->withErrors(['cancellation' => $exception->getMessage()]);
        }
    }
}
