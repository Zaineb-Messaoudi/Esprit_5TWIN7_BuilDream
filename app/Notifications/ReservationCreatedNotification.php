<?php

namespace App\Notifications;

use App\Models\Reservation;

/**
 * Fired when a buyer submits a reservation request.
 * Delivered to the equipment owner.
 */
class ReservationCreatedNotification extends BaseNotification
{
    public function __construct(
        public Reservation $reservation,
    ) {
        $this->recipientId = $reservation->equipment->owner_id;
    }

    public function title(): string
    {
        return __('New reservation request');
    }

    public function body(): string
    {
        return __(':name requested :equipment from :start to :end', [
            'name' => $this->reservation->user->name,
            'equipment' => $this->reservation->equipment->name,
            'start' => $this->reservation->start_date->format('M j, Y'),
            'end' => $this->reservation->end_date->format('M j, Y'),
        ]);
    }

    public function icon(): string
    {
        return 'calendar';
    }

    public function color(): string
    {
        return 'warning';
    }

    public function actionUrl(): ?string
    {
        return route('front.my-reservation-detail', $this->reservation->reference, absolute: false);
    }

    public function actionLabel(): ?string
    {
        return __('Review request');
    }

    public function emailPreferenceKey(): string
    {
        return 'reservation_created';
    }
}
