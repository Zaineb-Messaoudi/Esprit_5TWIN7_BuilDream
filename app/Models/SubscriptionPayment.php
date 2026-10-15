<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Payment for a subscription billing cycle.
 */
class SubscriptionPayment extends Model
{
    use HasFactory;

    protected $table = 'subscription_payments';

    protected $fillable = [
        'subscription_id',
        'payment_number',
        'amount',
        'status',
        'payment_method',
        'transaction_reference',
        'billing_period_start',
        'billing_period_end',
        'paid_at',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'billing_period_start' => 'datetime',
            'billing_period_end' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    /** The subscription this payment is for. */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /** Check if payment is successful. */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /** Scope for pending payments. */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
