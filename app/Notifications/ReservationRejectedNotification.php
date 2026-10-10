<?php

namespace App\Notifications;

use App\Models\Reservation;

/** Fired when the owner declines a reservation; notifies the buyer. */
class ReservationRejectedNotification extends BaseNotification
{
    public function __construct(
        public Reservation $reservation,
    ) {
        $this->recipientId = $reservation->user_id;
    }

    public function title(): string
    {
        return __('Reservation declined');
    }

    public function body(): string
    {
        return __('Your request for :equipment was declined by the owner.', [
            'equipment' => $this->reservation->equipment->name,
        ]);
    }

    public function icon(): string
    {
        return 'x-circle';
    }

    public function color(): string
    {
        return 'error';
    }

    public function actionUrl(): ?string
    {
        return route('front.catalog', absolute: false);
    }

    public function actionLabel(): ?string
    {
        return __('Browse other equipment');
    }

    public function emailPreferenceKey(): string
    {
        return 'reservation_rejected';
    }
}
