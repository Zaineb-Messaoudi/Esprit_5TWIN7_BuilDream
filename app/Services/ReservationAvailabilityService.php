<?php

namespace App\Services;

use App\Enums\RentalStatus;
use App\Models\Rental;
use App\Models\Reservation;
use Illuminate\Validation\ValidationException;

class ReservationAvailabilityService
{
    public function assertAvailable(
        int $equipmentId,
        string $startDate,
        string $endDate,
        ?int $ignoreReservationId = null
    ): void {
        $reservationOverlap = Reservation::query()
            ->where('equipment_id', $equipmentId)
            ->where('status', '!=', 'cancelled')
            ->when($ignoreReservationId, fn ($query) => $query->where('id', '!=', $ignoreReservationId))
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->exists();

        $rentalOverlap = Rental::query()
            ->where('equipment_id', $equipmentId)
            ->whereNotIn('status', [RentalStatus::CANCELLED->value, RentalStatus::COMPLETED->value])
            ->when($ignoreReservationId, fn ($query) => $query->whereDoesntHave(
                'reservation',
                fn ($reservation) => $reservation->whereKey($ignoreReservationId)
            ))
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->exists();

        if ($reservationOverlap || $rentalOverlap) {
            throw ValidationException::withMessages([
                'start_date' => __('This equipment is already reserved for the selected dates.'),
            ]);
        }
    }
}
