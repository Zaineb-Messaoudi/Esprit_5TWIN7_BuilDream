<?php

namespace App\Events;

use App\Models\Rental;
use App\Models\Inspection;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EquipmentReturned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Rental $rental,
        public Inspection $inspection,
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
        return 'equipment.returned';
    }

    public function broadcastWith(): array
    {
        $damageText = $this->inspection->damage_detected ? ' (damage detected)' : ' (no damage)';
        return [
            'rental' => $this->rental->load('equipment'),
            'inspection' => $this->inspection,
            'message' => "Equipment {$this->rental->equipment->name} returned{$damageText}. Rental {$this->rental->reference} completed.",
        ];
    }
}