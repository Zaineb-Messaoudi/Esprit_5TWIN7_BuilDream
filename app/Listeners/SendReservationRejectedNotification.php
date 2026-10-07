<?php

namespace App\Listeners;

use App\Events\ReservationRejected;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendReservationRejectedNotification
{
    public function handle(ReservationRejected $event): void
    {
        // Event is automatically broadcast via ShouldBroadcast
    }
}