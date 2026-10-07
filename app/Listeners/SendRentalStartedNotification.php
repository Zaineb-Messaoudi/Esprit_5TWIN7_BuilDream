<?php

namespace App\Listeners;

use App\Events\RentalStarted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendRentalStartedNotification
{
    public function handle(RentalStarted $event): void
    {
        // Event is automatically broadcast via ShouldBroadcast
    }
}