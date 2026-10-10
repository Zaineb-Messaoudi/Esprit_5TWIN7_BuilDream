<?php

namespace App\Events;

use App\Models\RentalExtension;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExtensionRequested implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public RentalExtension $extension,
        public User $owner
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('owner.'.$this->owner->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'extension.requested';
    }

    public function broadcastWith(): array
    {
        return [
            'extension' => $this->extension->load('rental.equipment', 'rental.user'),
            'message' => "Extension requested for {$this->extension->rental->equipment->name}: {$this->extension->reason}",
        ];
    }
}
