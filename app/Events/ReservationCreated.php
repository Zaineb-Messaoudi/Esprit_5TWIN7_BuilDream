<?php

namespace App\Events;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReservationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Reservation $reservation,
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
        return 'reservation.created';
    }

    public function broadcastWith(): array
    {
        return [
            'reservation' => $this->reservation->load('equipment', 'user'),
            'message' => "New reservation request for {$this->reservation->equipment->name} from {$this->reservation->user->name}",
        ];
    }
}
