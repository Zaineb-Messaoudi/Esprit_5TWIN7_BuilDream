<?php

namespace App\Listeners;

use App\Events\ReservationCreated;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendReservationCreatedNotification
{
    public function handle(ReservationCreated $event): void
    {
        // Event is automatically broadcast via ShouldBroadcast
        // This listener can be used for additional logic like sending emails
    }
}