<?php

namespace App\Listeners;

use App\Events\ReservationRejected;
use App\Notifications\ReservationRejectedNotification;

/** Fires when the owner declines a reservation; notifies the buyer. */
class SendReservationRejectedNotification
{
    public function handle(ReservationRejected $event): void
    {
        $buyer = $event->buyer;

        if (! $buyer) {
            return;
        }

        $buyer->notify(new ReservationRejectedNotification($event->reservation));
    }
}
