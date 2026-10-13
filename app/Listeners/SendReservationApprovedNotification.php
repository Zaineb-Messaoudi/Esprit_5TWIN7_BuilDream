<?php

namespace App\Listeners;

use App\Events\ReservationApproved;
use App\Notifications\ReservationApprovedNotification;

/** Fires when the owner approves a reservation; notifies the buyer. */
class SendReservationApprovedNotification
{
    public function handle(ReservationApproved $event): void
    {
        $buyer = $event->buyer;

        if (! $buyer) {
            return;
        }

        $buyer->notify(new ReservationApprovedNotification($event->reservation));
    }
}
