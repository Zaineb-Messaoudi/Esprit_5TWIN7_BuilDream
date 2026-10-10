<?php

namespace App\Listeners;

use App\Events\ExtensionRejected;
use App\Notifications\ExtensionRejectedNotification;

/** Fires when the owner rejects a rental extension; notifies the buyer. */
class SendExtensionRejectedNotification
{
    public function handle(ExtensionRejected $event): void
    {
        $buyer = $event->buyer;

        if (! $buyer) {
            return;
        }

        $buyer->notify(new ExtensionRejectedNotification($event->extension));
    }
}
