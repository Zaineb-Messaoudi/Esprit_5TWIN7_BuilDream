<?php

namespace App\Listeners;

use App\Events\ExtensionRequested;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendExtensionRequestedNotification
{
    public function handle(ExtensionRequested $event): void
    {
        // Event is automatically broadcast via ShouldBroadcast
    }
}