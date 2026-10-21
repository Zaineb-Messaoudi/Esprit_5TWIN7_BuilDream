<?php

namespace App\Services;

use App\Enums\RentalStatus;
use App\Models\Rental;
use App\Models\Reservation;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Availability check of a rental EXTENSION (advanced feature of the rental module).
 *
 * An extension is refused automatically when another booking of the SAME equipment
 * is planned during the extra period, that is from the day AFTER the current end date
 * until the requested end date (both days included).
 *
 * Used in three places: when the renter requests an extension, and when the owner
 * or the administrator approves it (a booking may have appeared in the meantime).
 */
class ExtensionAvailabilityService
{
    /**
     * Bookings that overlap the extra period.
     * Returns a collection of arrays: ['type' => 'reservation'|'rental', 'reference', 'start', 'end'].
     * An empty collection means that the equipment is free.
     *
     * $newEnd is typed CarbonInterface on purpose: the controllers give either a
     * Carbon\Carbon or an Illuminate\Support\Carbon, and both implement this interface.
     */
    public function conflicts(Rental $rental, CarbonInterface $newEnd): Collection
    {
        // The extra period: day after the current end date -> requested end date
        $from = $rental->end_date->copy()->addDay()->startOfDay();
        $to = $newEnd->copy()->startOfDay();

        // The requested date is not after the current end: there is no extra period
        if ($to->lt($from)) {
            return collect();
        }

        // 1) Reservations of this equipment that are not cancelled.
        //    A reservation that already became a rental is counted through that rental
        //    (its dates may have changed with extensions), hence whereDoesntHave('rental').
        //    Two periods overlap when: start1 <= end2 AND end1 >= start2.
        $reservations = Reservation::query()
            ->where('equipment_id', $rental->equipment_id)
            ->where('status', '!=', 'cancelled')
            ->whereDoesntHave('rental')
            ->where('start_date', '<=', $to->toDateString())
            ->where('end_date', '>=', $from->toDateString())
            ->get()
            ->map(fn (Reservation $reservation) => [
                'type' => 'reservation',
                'reference' => $reservation->reference,
                'start' => $reservation->start_date->copy(),
                'end' => $reservation->end_date->copy(),
            ]);

        // 2) Other rentals of this equipment that are not finished or cancelled
        $rentals = Rental::query()
            ->where('equipment_id', $rental->equipment_id)
            ->whereKeyNot($rental->id)                       // not the rental we are extending
            ->whereNotIn('status', [RentalStatus::CANCELLED->value, RentalStatus::COMPLETED->value])
            ->where('start_date', '<=', $to->toDateString())
            ->where('end_date', '>=', $from->toDateString())
            ->get()
            ->map(fn (Rental $other) => [
                'type' => 'rental',
                'reference' => $other->reference,
                'start' => $other->start_date->copy(),
                'end' => $other->end_date->copy(),
            ]);

        return $reservations->concat($rentals)->values();
    }

    /** Short text listing the conflicts, e.g. "RES-AB12 (14/10/2026 → 16/10/2026)". */
    public function describe(Collection $conflicts): string
    {
        return $conflicts
            ->map(fn (array $c) => sprintf('%s (%s → %s)', $c['reference'], $c['start']->format('d/m/Y'), $c['end']->format('d/m/Y')))
            ->implode('; ');
    }

    /**
     * Message shown to the user when the extension is refused.
     * It also tells how far the rental could be extended, when it is possible at all.
     */
    public function refusalMessage(Rental $rental, Collection $conflicts): string
    {
        // The first booking in time is the one that limits the extension
        $first = $conflicts->sortBy(fn (array $c) => $c['start']->timestamp)->first();
        $latestPossibleEnd = $first['start']->copy()->subDay();

        if ($latestPossibleEnd->gt($rental->end_date)) {
            return sprintf(
                'The equipment is already booked from %s (%s). You can extend until %s at most.',
                $first['start']->format('d/m/Y'),
                $first['reference'],
                $latestPossibleEnd->format('d/m/Y')
            );
        }

        return sprintf(
            'The equipment is already booked right after the current end date (%s), so this rental cannot be extended.',
            $first['reference']
        );
    }
}
