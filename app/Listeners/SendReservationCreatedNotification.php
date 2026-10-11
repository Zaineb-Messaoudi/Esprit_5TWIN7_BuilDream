<?php

namespace App\Listeners;

use App\Events\ReservationCreated;
use App\Notifications\ReservationCreatedNotification;

/**
 * Fires when a buyer submits a reservation request.
 *
 * The equipment owner is notified through every available channel
 * (database inbox, email, real-time Pusher broadcast).
 */
class SendReservationCreatedNotification
{
    public function handle(ReservationCreated $event): void
    {
        $owner = $event->owner;

        if (! $owner) {
            return;
        }

        $owner->notify(new ReservationCreatedNotification($event->reservation));
    }
}
