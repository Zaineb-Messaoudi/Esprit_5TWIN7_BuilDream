<?php

namespace App\Events;

use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MaintenanceCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Maintenance $maintenance,
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
        return 'maintenance.completed';
    }

    public function broadcastWith(): array
    {
        return [
            'maintenance' => $this->maintenance->load('equipment'),
            'message' => "Maintenance completed for {$this->maintenance->equipment->name}. Equipment is available again.",
        ];
    }
}