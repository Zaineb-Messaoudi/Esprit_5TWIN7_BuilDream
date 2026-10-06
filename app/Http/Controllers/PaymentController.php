<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Rental;
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
        $payment = Payment::create($data);
        if ($payment->status === 'paid') {
            $reservation->update(['status' => 'confirmed']);
            $this->createRentalFromReservation($reservation);
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
        abort_unless(request()->user()->isAdmin(), 403);
        $payment->update($request->validate(['status' => ['required', 'in:pending,paid,failed']]));
        if ($payment->status === 'paid') {
            $payment->reservation->update(['status' => 'confirmed']);
            $this->createRentalFromReservation($payment->reservation);
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
}
