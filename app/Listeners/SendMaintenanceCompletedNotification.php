<?php

namespace App\Listeners;

use App\Events\MaintenanceCompleted;
use App\Notifications\MaintenanceCompletedNotification;

/** Fires when maintenance is completed; notifies the owner. */
class SendMaintenanceCompletedNotification
{
    public function handle(MaintenanceCompleted $event): void
    {
        $owner = $event->owner;

        if (! $owner) {
            return;
        }

        $event->maintenance->load('equipment');

        $owner->notify(new MaintenanceCompletedNotification($event->maintenance));
    }
}
