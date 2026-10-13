<?php

namespace App\Events;

use App\Models\RentalExtension;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExtensionRejected implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public RentalExtension $extension,
        public User $buyer
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.'.$this->buyer->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'extension.rejected';
    }

    public function broadcastWith(): array
    {
        return [
            'extension' => $this->extension->load('rental.equipment'),
            'message' => "Your extension request for {$this->extension->rental->equipment->name} was rejected.",
        ];
    }
}
