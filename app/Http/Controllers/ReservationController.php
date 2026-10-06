<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Requests\ReservationRequest;
use Illuminate\Http\JsonResponse;

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

    $reservation = Reservation::create($data);

    return redirect()->route('rental.reservations.index')->with('success', __('Reservation created successfully.'));
}


    public function show(Reservation $reservation): JsonResponse
    {
        abort_unless(request()->user()->isAdmin() || $reservation->user_id === request()->user()->id, 403);
        $reservation->load(['equipment', 'user', 'payments', 'invoice']);
        return view('reservations.show', compact('reservation'));
    }

    public function edit(Reservation $reservation)
    {
        abort_unless(request()->user()->isAdmin() || $reservation->user_id === request()->user()->id, 403);
        return view('reservations.edit', ['reservation' => $reservation, 'equipments' => Equipment::all()]);
    }

    public function update(ReservationRequest $request, Reservation $reservation)
{
    abort_unless(request()->user()->isAdmin() || $reservation->user_id === request()->user()->id, 403);
    $data = $request->validated();
    unset($data['user_id']);
    $reservation->update($data);

    return redirect()->route('rental.reservations.index')->with('success', __('Reservation updated successfully.'));
}
    

    public function destroy(Reservation $reservation)
    {
        abort_unless(request()->user()->isAdmin() || $reservation->user_id === request()->user()->id, 403);
        $reservation->delete();
        return response()->noContent();
    }
}
