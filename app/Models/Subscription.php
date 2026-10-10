<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Recurring rental subscription.
 */
class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'subscriptions';

    protected $fillable = [
        'subscription_plan_id',
        'equipment_id',
        'renter_id',
        'owner_id',
        'subscription_number',
        'starts_at',
        'next_billing_at',
        'ends_at',
        'status',
        'current_price_per_cycle',
        'billing_cycles_completed',
        'pauses_remaining',
        'paused_at',
        'cancelled_at',
        'cancellation_reason',
        'paused_cycles',
    ];

    protected function casts(): array
    {
        return [
            'current_price_per_cycle' => 'decimal:2',
            'starts_at' => 'datetime',
            'next_billing_at' => 'datetime',
            'ends_at' => 'datetime',
            'paused_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'paused_cycles' => 'array',
        ];
    }

    /** The subscription plan. */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    /** The equipment being subscribed to. */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /** The renter (subscriber). */
    public function renter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'renter_id');
    }

    /** The equipment owner. */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** Payments for this subscription. */
    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class)->latest();
    }

    /** Check if subscription is active. */
    public function isActive(): bool
    {
        return $this->status === 'active'
            && $this->starts_at <= now()
            && ($this->ends_at === null || $this->ends_at >= now());
    }

    /** Check if subscription is paused. */
    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    /** Check if subscription can be paused. */
    public function canPause(): bool
    {
        return $this->plan->allow_pause
            && $this->status === 'active'
            && $this->pauses_remaining > 0;
    }

    /** Pause the subscription. */
    public function pause(?int $days = null): bool
    {
        if (! $this->canPause()) {
            return false;
        }

        $this->update([
            'status' => 'paused',
            'paused_at' => now(),
            'pauses_remaining' => $this->pauses_remaining - 1,
        ]);

        if ($days) {
            $resumeAt = now()->addDays($days);
            $this->paused_cycles = array_merge($this->paused_cycles ?? [], [
                ['paused_at' => now()->toISOString(), 'resume_at' => $resumeAt->toISOString()],
            ]);
            $this->save();
        }

        return true;
    }

    /** Resume a paused subscription. */
    public function resume(): bool
    {
        if ($this->status !== 'paused') {
            return false;
        }

        $this->update([
            'status' => 'active',
            'paused_at' => null,
        ]);

        return true;
    }

    /** Cancel the subscription. */
    public function cancel(?string $reason = null): bool
    {
        if (! in_array($this->status, ['active', 'paused'])) {
            return false;
        }

        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        return true;
    }

    /** Get next billing amount. */
    public function getNextBillingAmount(): float
    {
        return $this->current_price_per_cycle;
    }

    /** Scope for active subscriptions. */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }

    /** Scope for subscriptions due for billing. */
    public function scopeDueForBilling($query)
    {
        return $query->active()
            ->where('next_billing_at', '<=', now()->addDay());
    }
}
