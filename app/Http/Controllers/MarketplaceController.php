<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\Reservation;
use App\Models\RentalContract;
use App\Events\ReservationCreated;
use App\Events\ReservationApproved;
use App\Events\ReservationRejected;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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

        $equipment = Equipment::query()
            ->where('approval_status', 'published')
            ->where('status', 'available')
            ->findOrFail($data['equipment_id']);

        $start = $data['start_date'];
        $end = $data['end_date'];
        $reservationOverlap = Reservation::query()
            ->where('equipment_id', $equipment->id)
            ->where('status', '!=', 'cancelled')
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start)
            ->exists();
        $rentalOverlap = Rental::query()
            ->where('equipment_id', $equipment->id)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start)
            ->exists();

        if ($reservationOverlap || $rentalOverlap) {
            throw ValidationException::withMessages([
                'start_date' => __('This equipment is already reserved for the selected dates.'),
            ]);
        }

        $days = max(1, Carbon::parse($start)->startOfDay()->diffInDays(Carbon::parse($end)->startOfDay()) + 1);
        $reservation = Reservation::create([
            'reference' => 'RES-'.strtoupper(Str::random(8)),
            'equipment_id' => $equipment->id,
            'user_id' => $request->user()->id,
            'start_date' => $start,
            'end_date' => $end,
            'total_amount' => round((float) $equipment->price_per_day * $days, 2),
            'status' => 'pending',
        ]);

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

        DB::transaction(function () use ($reservation, $data): void {
            $paid = $reservation->payments()->where('status', 'paid')->exists();
            if ($paid) {
                return;
            }

            $existing = $reservation->payments()->where('status', 'pending')->latest()->first();
            if ($existing) {
                $existing->update(['payment_method' => $data['payment_method']]);
                return;
            }

            $reservation->payments()->create([
                'amount' => round((float) $reservation->total_amount * 1.19, 2),
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

    public function signContract(Request $request, RentalContract $contract): RedirectResponse
    {
        $contract->loadMissing('rental');
        abort_unless((int) $contract->rental->user_id === (int) $request->user()->id, 403);
        abort_unless($contract->contract_status->value === 'draft', 409);

        $contract->update(['contract_status' => 'signed', 'signed_at' => now()]);

        return back()->with('status', __('Rental contract signed.'));
    }
}
