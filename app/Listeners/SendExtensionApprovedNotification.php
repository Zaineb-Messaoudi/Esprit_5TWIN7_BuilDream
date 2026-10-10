<?php

namespace App\Listeners;

use App\Events\ExtensionApproved;
use App\Notifications\ExtensionApprovedNotification;

/** Fires when the owner approves a rental extension; notifies the buyer. */
class SendExtensionApprovedNotification
{
    public function handle(ExtensionApproved $event): void
    {
        $buyer = $event->buyer;

        if (! $buyer) {
            return;
        }

        $buyer->notify(new ExtensionApprovedNotification($event->extension));
    }
}
