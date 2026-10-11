<?php

namespace App\Listeners;

use App\Events\ExtensionRequested;
use App\Notifications\ExtensionRequestedNotification;

/** Fires when a buyer requests a rental extension; notifies the owner. */
class SendExtensionRequestedNotification
{
    public function handle(ExtensionRequested $event): void
    {
        $owner = $event->owner;

        if (! $owner) {
            return;
        }

        $owner->notify(new ExtensionRequestedNotification($event->extension));
    }
}
