<?php

namespace App\Events;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Payment $payment,
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
        return 'payment.received';
    }

    public function broadcastWith(): array
    {
        return [
            'payment' => $this->payment->load('reservation.equipment'),
            'message' => "Payment of {$this->payment->amount} TND received for {$this->payment->reservation->equipment->name}",
        ];
    }
}
