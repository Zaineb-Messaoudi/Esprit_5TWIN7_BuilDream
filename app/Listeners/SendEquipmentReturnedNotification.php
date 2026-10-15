<?php

namespace App\Listeners;

use App\Events\EquipmentReturned;
use App\Notifications\EquipmentReturnedNotification;

/**
 * Fires when equipment is returned and inspected.
 *
 * The event carries both the owner and the buyer, so each recipient is
 * notified separately through database inbox, email and real-time broadcast.
 */
class SendEquipmentReturnedNotification
{
    public function handle(EquipmentReturned $event): void
    {
        $event->rental->load('equipment');

        $recipients = array_filter([
            $event->owner,
            $event->buyer,
        ]);

        foreach ($recipients as $recipient) {
            $notification = new EquipmentReturnedNotification($event->rental, $event->inspection);
            $notification->recipientId = $recipient->id;

            $recipient->notify($notification);
        }
    }
}
