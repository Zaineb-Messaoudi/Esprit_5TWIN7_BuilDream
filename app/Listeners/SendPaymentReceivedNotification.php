<?php

namespace App\Listeners;

use App\Events\PaymentReceived;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPaymentReceivedNotification
{
    public function handle(PaymentReceived $event): void
    {
        // Event is automatically broadcast via ShouldBroadcast
    }
}