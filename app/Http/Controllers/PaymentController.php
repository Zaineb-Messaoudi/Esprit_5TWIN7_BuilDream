<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Rental;
use App\Models\Invoice;
use App\Models\RentalContract;
use App\Events\PaymentReceived;
use App\Events\RentalStarted;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
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
        if (! request()->user()->isAdmin()) $query->where('user_id', request()->user()->id);
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
        $data['transaction_reference'] = 'PAY-'.strtoupper(Str::random(10));
        if (! request()->user()->isAdmin()) {
            $data['amount'] = round((float) $reservation->total_amount * 1.19, 2);
            $data['payment_date'] = now();
            $data['status'] = 'pending';
        }
        DB::transaction(function () use ($data, $reservation): void {
            $payment = Payment::create($data);
            if ($payment->status === 'paid') {
                $this->completePaidReservation($reservation, $payment->amount);
            }
        });
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
        abort_unless(request()->user()->isAdmin() || $payment->reservation->user_id === request()->user()->id, 403);
        return view('payments.edit', compact('payment'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $payment = Payment::findOrFail($id);
        abort_unless(request()->user()->isAdmin() || $payment->reservation->user_id === request()->user()->id, 403);
        $data = $request->validate(['status' => ['required', 'in:pending,paid,failed']]);
        $wasPaid = $payment->status === 'paid';
        DB::transaction(function () use ($payment, $data, $wasPaid): void {
            $payment->update($data);
            if ($payment->status === 'paid' && ! $wasPaid) {
                $this->completePaidReservation($payment->reservation, $payment->amount);
            }
        });
        
        // Fire real-time notification when payment is marked as paid
        if ($payment->status === 'paid' && ! $wasPaid) {
            PaymentReceived::dispatch($payment->load('reservation.equipment'), $payment->reservation->equipment->owner);
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

    private function createRentalFromReservation(Reservation $reservation): void
    {
        Rental::firstOrCreate(
            ['reservation_id' => $reservation->id],
            [
                'reference' => 'RNT-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
                'equipment_id' => $reservation->equipment_id,
                'user_id' => $reservation->user_id,
                'start_date' => $reservation->start_date,
                'end_date' => $reservation->end_date,
                'total_amount' => $reservation->total_amount,
                'status' => 'pending',
            ],
        );
    }

    private function completePaidReservation(Reservation $reservation, float|string $amount): void
    {
        abort_unless($reservation->status === 'confirmed', 422, __('The reservation must be approved by its equipment owner before payment can be verified.'));
        $this->createRentalFromReservation($reservation);

        $rental = Rental::where('reservation_id', $reservation->id)->firstOrFail();
        $rental->update(['total_amount' => $amount]);

        RentalContract::firstOrCreate(
            ['rental_id' => $rental->id],
            [
                'contract_number' => 'CTR-'.now()->format('Y').'-'.strtoupper(Str::random(8)),
                'terms' => __('The renter agrees to use the equipment safely and return it in the same condition by the agreed end date.'),
                'deposit_amount' => 0,
                'contract_status' => 'draft',
            ],
        );

        $invoice = Invoice::firstOrNew(['reservation_id' => $reservation->id]);
        $invoice->fill([
            'invoice_number' => $invoice->invoice_number ?: 'INV-'.strtoupper(Str::random(10)),
            'issue_date' => $invoice->issue_date ?: now()->toDateString(),
            'subtotal' => $reservation->total_amount,
            'tax' => round((float) $reservation->total_amount * 0.19, 2),
            'total' => $amount,
            'status' => 'paid',
        ])->save();

        // Fire real-time notification for rental started
        $rental->load('equipment', 'user');
        RentalStarted::dispatch($rental, $rental->equipment->owner, $rental->user);
    }
}
