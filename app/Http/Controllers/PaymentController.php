<?php

namespace App\Http\Controllers;

use App\Events\PaymentReceived;
use App\Events\RentalStarted;
use App\Http\Requests\PaymentRequest;
use App\Models\Payment;
use App\Models\Reservation;
use App\Services\ReservationPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Payment::with('reservation.equipment');
        if (! request()->user()->isAdmin()) {
            $query->whereHas('reservation', fn ($q) => $q->where('user_id', request()->user()->id));
        }

        return view('payments.index', ['payments' => $query->latest()->paginate(10)]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $query = Reservation::query();
        if (! request()->user()->isAdmin()) {
            $query->where('user_id', request()->user()->id);
        }

        return view('payments.create', ['reservations' => $query->with('equipment')->get()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaymentRequest $request)
    {
        $data = $request->validated();
        $reservation = Reservation::findOrFail($data['reservation_id']);
        abort_unless(request()->user()->isAdmin() || $reservation->user_id === request()->user()->id, 403);
        $paymentPlan = app(ReservationPaymentService::class);
        $data['transaction_reference'] = 'PAY-'.strtoupper(Str::random(10));
        if (! request()->user()->isAdmin()) {
            abort_unless($reservation->status === 'confirmed', 409);
            $data['amount'] = $paymentPlan->nextPaymentAmount($reservation);
            abort_if($data['amount'] <= 0, 409, __('This reservation is already fully paid.'));
            $data['payment_stage'] = $paymentPlan->nextPaymentStage($reservation);
            $data['due_date'] = $paymentPlan->nextPaymentDueDate($reservation);
            $data['payment_date'] = now();
            $data['status'] = 'pending';
        }
        $payment = null;
        $reservationFullyPaid = false;
        DB::transaction(function () use ($data, $reservation, $paymentPlan, &$payment, &$reservationFullyPaid): void {
            $lockedReservation = Reservation::query()->whereKey($reservation->id)->lockForUpdate()->firstOrFail();
            $hasPendingPayment = $lockedReservation->payments()->where('status', 'pending')->exists();
            abort_if($hasPendingPayment, 409, __('A payment is already pending for this reservation.'));
            abort_unless($lockedReservation->status === 'confirmed', 409, __('The reservation must be approved before a payment can be recorded.'));
            if (request()->user()->isAdmin()) {
                abort_if(
                    round((float) $data['amount'], 2) > $paymentPlan->remainingAmount($lockedReservation),
                    422,
                    __('The payment amount exceeds the remaining reservation balance.')
                );
            }
            $payment = Payment::create($data);
            if ($payment->status === 'paid') {
                $reservationFullyPaid = app(ReservationPaymentService::class)
                    ->synchronizeAfterPayment($lockedReservation);
            }
        });

        if ($payment?->status === 'paid') {
            PaymentReceived::dispatch($payment->load('reservation.equipment'), $payment->reservation->equipment->owner);
        }
        if ($reservationFullyPaid) {
            $rental = $reservation->rental()->with('equipment', 'user')->firstOrFail();
            RentalStarted::dispatch($rental, $rental->equipment->owner, $rental->user);
        }

        return redirect()->route('rental.payments.index')->with('success', __('Payment recorded successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        abort_unless(request()->user()->isAdmin() || $payment->reservation->user_id === request()->user()->id, 403);

        return view('payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $payment = Payment::findOrFail($id);
        abort_unless(request()->user()->isAdmin(), 403);

        return view('payments.edit', compact('payment'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $payment = Payment::findOrFail($id);
        abort_unless(request()->user()->isAdmin(), 403);
        $data = $request->validate(['status' => ['required', 'in:pending,paid,failed']]);
        $wasPaid = $payment->status === 'paid';
        abort_unless(! $wasPaid || $data['status'] === 'paid', 409);
        $reservationFullyPaid = false;
        DB::transaction(function () use (&$payment, $data, &$wasPaid, &$reservationFullyPaid): void {
            $lockedReservation = Reservation::query()
                ->whereKey($payment->reservation_id)
                ->lockForUpdate()
                ->firstOrFail();
            $payment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $wasPaid = $payment->status === 'paid';
            abort_unless(! $wasPaid || $data['status'] === 'paid', 409);
            if ($data['status'] === 'paid' && ! $wasPaid) {
                abort_if(
                    $lockedReservation->payments()
                        ->where('status', 'pending')
                        ->where('id', '!=', $payment->id)
                        ->exists(),
                    409,
                    __('Another payment is still pending for this reservation.')
                );
                abort_if(
                    round((float) $payment->amount, 2) > app(ReservationPaymentService::class)->remainingAmount($lockedReservation),
                    422,
                    __('The payment amount exceeds the remaining reservation balance.')
                );
            }
            $payment->update($data);
            if ($payment->status === 'paid' && ! $wasPaid) {
                $reservationFullyPaid = app(ReservationPaymentService::class)
                    ->synchronizeAfterPayment($lockedReservation);
            }
        });

        if ($payment->status === 'paid' && ! $wasPaid) {
            PaymentReceived::dispatch($payment->load('reservation.equipment'), $payment->reservation->equipment->owner);
        }
        if ($reservationFullyPaid) {
            $rental = $payment->reservation->rental()->with('equipment', 'user')->firstOrFail();
            RentalStarted::dispatch($rental, $rental->equipment->owner, $rental->user);
        }

        return redirect()->route('rental.payments.index')->with('success', __('Payment updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $payment = Payment::findOrFail($id);
        abort_unless(request()->user()->isAdmin() || $payment->reservation->user_id === request()->user()->id, 403);
        abort_unless($payment->status !== 'paid', 409);
        $payment->delete();

        return redirect()->route('rental.payments.index')->with('success', __('Payment deleted successfully.'));
    }
}
