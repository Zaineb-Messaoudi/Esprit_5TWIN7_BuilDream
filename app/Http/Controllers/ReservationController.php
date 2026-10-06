<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Requests\ReservationRequest;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class ReservationController extends Controller
{
    public function index()
{
    $query = Reservation::with(['equipment', 'user'])->latest();
    if (! request()->user()->isAdmin()) {
        $query->where('user_id', request()->user()->id);
    }

    return view('reservations.index', ['reservations' => $query->paginate(10)]);
}

    public function create()
    {
        return view('reservations.create', ['equipments' => Equipment::all()]);
    }

    public function store(ReservationRequest $request)
{
    $data = $request->validated();
    $data['reference'] = 'RES-' . strtoupper(Str::random(8));
    $data['user_id'] = request()->user()->isAdmin() && isset($data['user_id'])
        ? $data['user_id'] : request()->user()->id;
    $data['status'] = 'pending';
    $data['total_amount'] = $this->computeTotalAmount(
        (int) $data['equipment_id'],
        (string) $data['start_date'],
        (string) $data['end_date']
    );

    $reservation = Reservation::create($data);

    return redirect()->route('rental.reservations.index')->with('success', __('Reservation created successfully.'));
}


    public function show(Reservation $reservation): View
    {
        abort_unless(request()->user()->isAdmin() || $reservation->user_id === request()->user()->id, 403);
        $reservation->load(['equipment', 'user', 'payments', 'invoice']);
        return view('reservations.show', compact('reservation'));
    }

    public function edit(Reservation $reservation)
    {
        abort_unless(request()->user()->isAdmin() || $reservation->user_id === request()->user()->id, 403);
        if (! request()->user()->isAdmin()) {
            abort_unless($reservation->status === 'pending' && ! $reservation->payments()->exists(), 409);
        }
        return view('reservations.edit', ['reservation' => $reservation, 'equipments' => Equipment::all()]);
    }

    public function update(ReservationRequest $request, Reservation $reservation)
{
    abort_unless(request()->user()->isAdmin() || $reservation->user_id === request()->user()->id, 403);
    if (! request()->user()->isAdmin()) {
        abort_unless($reservation->status === 'pending' && ! $reservation->payments()->exists(), 409);
    }
    $data = $request->validated();
    unset($data['user_id'], $data['total_amount']);

    if (! request()->user()->isAdmin()) {
        unset($data['status']);
    }

    $equipmentId = isset($data['equipment_id']) ? (int) $data['equipment_id'] : (int) $reservation->equipment_id;
    $startDate = isset($data['start_date']) ? (string) $data['start_date'] : (string) $reservation->start_date;
    $endDate = isset($data['end_date']) ? (string) $data['end_date'] : (string) $reservation->end_date;
    $data['total_amount'] = $this->computeTotalAmount($equipmentId, $startDate, $endDate);

    $reservation->update($data);

    return redirect()->route('rental.reservations.index')->with('success', __('Reservation updated successfully.'));
}
    

    public function destroy(Reservation $reservation)
    {
        abort_unless(request()->user()->isAdmin() || $reservation->user_id === request()->user()->id, 403);
        abort_unless(! $reservation->payments()->where('status', 'paid')->exists() && ! $reservation->rental()->exists(), 409);
        $reservation->delete();
        return response()->noContent();
    }

    private function computeTotalAmount(int $equipmentId, string $startDate, string $endDate): float
    {
        $equipment = Equipment::findOrFail($equipmentId);
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();
        $days = max(1, $start->diffInDays($end) + 1);

        return (float) $equipment->price_per_day * $days;
    }
}
