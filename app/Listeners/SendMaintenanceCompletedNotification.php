<?php

namespace App\Listeners;

use App\Events\MaintenanceCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendMaintenanceCompletedNotification
{
    public function handle(MaintenanceCompleted $event): void
    {
        // Event is automatically broadcast via ShouldBroadcast
    }
}