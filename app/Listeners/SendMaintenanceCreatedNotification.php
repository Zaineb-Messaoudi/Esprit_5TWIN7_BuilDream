<?php

namespace App\Listeners;

use App\Events\MaintenanceCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendMaintenanceCreatedNotification
{
    public function handle(MaintenanceCreated $event): void
    {
        // Event is automatically broadcast via ShouldBroadcast
    }
}