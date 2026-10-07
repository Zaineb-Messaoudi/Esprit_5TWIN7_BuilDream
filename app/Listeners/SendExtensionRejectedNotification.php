<?php

namespace App\Listeners;

use App\Events\ExtensionRejected;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendExtensionRejectedNotification
{
    public function handle(ExtensionRejected $event): void
    {
        // Event is automatically broadcast via ShouldBroadcast
    }
}