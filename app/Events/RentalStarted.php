<?php

namespace App\Events;

use App\Models\Rental;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RentalStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Rental $rental,
        public User $owner,
        public User $buyer
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('owner.'.$this->owner->id),
            new PrivateChannel('user.'.$this->buyer->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'rental.started';
    }

    public function broadcastWith(): array
    {
        return [
            'rental' => $this->rental->load('equipment', 'user'),
            'message' => "Rental {$this->rental->reference} has started for {$this->rental->equipment->name}",
        ];
    }
}