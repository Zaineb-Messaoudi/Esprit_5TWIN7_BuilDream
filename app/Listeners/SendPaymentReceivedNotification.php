<?php

namespace App\Listeners;

use App\Events\PaymentReceived;
use App\Notifications\PaymentReceivedNotification;

/** Fires when an administrator marks a payment as paid; notifies the owner. */
class SendPaymentReceivedNotification
{
    public function handle(PaymentReceived $event): void
    {
        $event->payment->load('reservation.equipment');

        $owner = $event->owner;

        if (! $owner) {
            return;
        }

        $owner->notify(new PaymentReceivedNotification($event->payment));
    }
}
