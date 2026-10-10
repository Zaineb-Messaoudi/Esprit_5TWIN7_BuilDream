<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Stripe webhook event log for debugging and idempotency.
 */
class StripeWebhookEvent extends Model
{
    use HasFactory;

    protected $table = 'stripe_webhook_events';

    protected $fillable = [
        'stripe_event_id',
        'type',
        'payload',
        'status',
        'error_message',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    /** Check if event has been processed. */
    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }

    /** Mark as processed. */
    public function markProcessed(): void
    {
        $this->update([
            'status' => 'processed',
            'processed_at' => now(),
        ]);
    }

    /** Mark as failed. */
    public function markFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
        ]);
    }

    /** Scope for pending events. */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /** Scope for failed events. */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
