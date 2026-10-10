<?php

namespace App\Events;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReservationRejected implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Reservation $reservation,
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
        return 'reservation.rejected';
    }

    public function broadcastWith(): array
    {
        return [
            'reservation' => $this->reservation->load('equipment'),
            'message' => "Your reservation for {$this->reservation->equipment->name} was declined.",
        ];
    }
}
