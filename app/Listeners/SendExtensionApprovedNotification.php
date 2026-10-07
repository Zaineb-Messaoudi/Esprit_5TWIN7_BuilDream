<?php

namespace App\Listeners;

use App\Events\ExtensionApproved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendExtensionApprovedNotification
{
    public function handle(ExtensionApproved $event): void
    {
        // Event is automatically broadcast via ShouldBroadcast
    }
}