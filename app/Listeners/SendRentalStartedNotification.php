<?php

namespace App\Listeners;

use App\Events\RentalStarted;
use App\Notifications\RentalStartedNotification;

/**
 * Fires when a rental starts after payment verification.
 *
 * The event carries both the owner and the buyer, so each recipient is
 * notified separately: the broadcast channel resolves to the user actually
 * receiving the notification, and the deep link points at their own rental.
 */
class SendRentalStartedNotification
{
    public function handle(RentalStarted $event): void
    {
        $event->rental->load('equipment');

        $recipients = array_filter([
            $event->owner,
            $event->buyer,
        ]);

        foreach ($recipients as $recipient) {
            $notification = new RentalStartedNotification($event->rental);
            $notification->recipientId = $recipient->id;

            $recipient->notify($notification);
        }
    }
}
