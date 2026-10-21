<?php

namespace App\Notifications;

use App\Models\Reservation;

/**
 * Fired when the equipment owner approves a reservation request.
 * Delivered to the buyer.
 */
class ReservationApprovedNotification extends BaseNotification
{
    public function __construct(
        public Reservation $reservation,
    ) {
        $this->recipientId = $reservation->user_id;
    }

    public function title(): string
    {
        return __('Reservation approved');
    }

    public function body(): string
    {
        return __('Your reservation for :equipment has been approved. Complete payment to confirm your rental.', [
            'equipment' => $this->reservation->equipment->name,
        ]);
    }

    public function icon(): string
    {
        return 'check-circle';
    }

    public function color(): string
    {
        return 'success';
    }

    public function actionUrl(): ?string
    {
        return route('front.booking-summary', [
            'equipment' => $this->reservation->equipment_id,
            'reservation' => $this->reservation->id,
        ], absolute: false);
    }

    public function actionLabel(): ?string
    {
        return __('Pay now');
    }

    public function emailPreferenceKey(): string
    {
        return 'reservation_approved';
    }
}
