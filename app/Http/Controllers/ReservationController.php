<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReservationRequest;
use App\Models\Equipment;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        $data['reference'] = 'RES-'.strtoupper(Str::random(8));
        $data['user_id'] = request()->user()->isAdmin() && isset($data['user_id'])
            ? $data['user_id'] : request()->user()->id;
        $data['status'] = 'pending';
        $reservation = DB::transaction(function () use ($data): Reservation {
            $equipment = Equipment::query()->lockForUpdate()->findOrFail($data['equipment_id']);
            $this->assertEquipmentCanBeReserved($equipment);
            app(\App\Services\ReservationAvailabilityService::class)->assertAvailable(
                $equipment->id,
                (string) $data['start_date'],
                (string) $data['end_date'],
            );
            $data['total_amount'] = $this->computeTotalAmount(
                $equipment->id,
                (string) $data['start_date'],
                (string) $data['end_date'],
            );

            return Reservation::create($data);
        });

        return redirect()->route('rental.reservations.index')->with('success', __('Reservation created successfully.'));
    }

    public function show(Reservation $reservation): View
    {
        abort_unless(request()->user()->isAdmin() || $reservation->user_id === request()->user()->id, 403);
        $reservation->load(['equipment', 'user', 'payments', 'invoice']);

        return view('reservations.show', [
            'reservation' => $reservation,
            'cancellationRequiresManualRefund' => app(\App\Services\ReservationCancellationService::class)
                ->requiresManualRefundConfirmation($reservation),
        ]);
    }

    public function edit(Reservation $reservation)
    {
        abort_unless(request()->user()->isAdmin() || $reservation->user_id === request()->user()->id, 403);
        abort_unless(
            ! $reservation->payments()->exists() && ! $reservation->rental()->exists(),
            409,
            __('Reservations with payment activity or a rental cannot be edited.')
        );
        if (! request()->user()->isAdmin()) {
            abort_unless($reservation->status === 'pending', 409);
        }

        return view('reservations.edit', ['reservation' => $reservation, 'equipments' => Equipment::all()]);
    }

    public function update(ReservationRequest $request, Reservation $reservation)
    {
        abort_unless(request()->user()->isAdmin() || $reservation->user_id === request()->user()->id, 403);
        $data = $request->validated();
        unset($data['user_id'], $data['total_amount']);

        if (! request()->user()->isAdmin()) {
            unset($data['status']);
        }

        DB::transaction(function () use ($data, $reservation): void {
            $lockedReservation = Reservation::query()->whereKey($reservation->id)->lockForUpdate()->firstOrFail();
            abort_unless(
                ! $lockedReservation->payments()->exists() && ! $lockedReservation->rental()->exists(),
                409,
                __('Reservations with payment activity or a rental cannot be edited.')
            );
            if (! request()->user()->isAdmin()) {
                abort_unless($lockedReservation->status === 'pending', 409);
            }

            $equipmentId = isset($data['equipment_id']) ? (int) $data['equipment_id'] : (int) $lockedReservation->equipment_id;
            $startDate = isset($data['start_date']) ? (string) $data['start_date'] : (string) $lockedReservation->start_date;
            $endDate = isset($data['end_date']) ? (string) $data['end_date'] : (string) $lockedReservation->end_date;
            $equipment = Equipment::query()->lockForUpdate()->findOrFail($equipmentId);
            if ($equipmentId !== (int) $lockedReservation->equipment_id) {
                $this->assertEquipmentCanBeReserved($equipment);
            }
            app(\App\Services\ReservationAvailabilityService::class)->assertAvailable(
                $equipment->id,
                $startDate,
                $endDate,
                $lockedReservation->id,
            );
            $data['total_amount'] = $this->computeTotalAmount($equipmentId, $startDate, $endDate);

            $lockedReservation->update($data);
        });

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

    private function assertEquipmentCanBeReserved(Equipment $equipment): void
    {
        if ($equipment->status !== 'available' || $equipment->approval_status !== 'published') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'equipment_id' => __('This equipment is not currently available for reservation.'),
            ]);
        }
    }
}
