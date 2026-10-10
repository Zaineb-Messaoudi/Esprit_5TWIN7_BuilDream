<?php

namespace App\Listeners;

use App\Events\MaintenanceCreated;
use App\Notifications\MaintenanceCreatedNotification;

/** Fires when maintenance starts on a piece of equipment; notifies the owner. */
class SendMaintenanceCreatedNotification
{
    public function handle(MaintenanceCreated $event): void
    {
        $owner = $event->owner;

        if (! $owner) {
            return;
        }

        $event->maintenance->load('equipment');

        $owner->notify(new MaintenanceCreatedNotification($event->maintenance));
    }
}
