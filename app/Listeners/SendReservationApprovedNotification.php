<?php

namespace App\Listeners;

use App\Events\ReservationApproved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendReservationApprovedNotification
{
    public function handle(ReservationApproved $event): void
    {
        // Event is automatically broadcast via ShouldBroadcast
    }
}