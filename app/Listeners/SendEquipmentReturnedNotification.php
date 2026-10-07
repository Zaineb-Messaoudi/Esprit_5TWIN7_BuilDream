<?php

namespace App\Listeners;

use App\Events\EquipmentReturned;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendEquipmentReturnedNotification
{
    public function handle(EquipmentReturned $event): void
    {
        // Event is automatically broadcast via ShouldBroadcast
    }
}