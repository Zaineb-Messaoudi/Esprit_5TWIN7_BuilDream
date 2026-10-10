<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Subscription plan (recurring rental product).
 */
class SubscriptionPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'subscription_plans';

    protected $fillable = [
        'equipment_id',
        'name',
        'slug',
        'description',
        'billing_cycle',
        'price_per_cycle',
        'discount_percentage',
        'min_commitment_cycles',
        'max_subscriptions',
        'auto_renew',
        'allow_pause',
        'pause_max_days',
        'included_services',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_per_cycle' => 'decimal:2',
            'discount_percentage' => 'decimal:2',
            'included_services' => 'array',
            'auto_renew' => 'boolean',
            'allow_pause' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /** The equipment this plan is for. */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /** Subscriptions on this plan. */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /** Get effective price per day (for comparison). */
    public function getEffectiveDailyPriceAttribute(): float
    {
        $daysPerCycle = match ($this->billing_cycle) {
            'weekly' => 7,
            'monthly' => 30,
            'quarterly' => 90,
            'yearly' => 365,
            default => 30,
        };

        return round(($this->price_per_cycle * (1 - $this->discount_percentage / 100)) / $daysPerCycle, 2);
    }

    /** Get the discount amount per cycle. */
    public function getDiscountAmountAttribute(): float
    {
        return round($this->price_per_cycle * ($this->discount_percentage / 100), 2);
    }

    /** Scope for active plans. */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Check if plan has capacity for new subscription. */
    public function hasCapacity(): bool
    {
        if (! $this->max_subscriptions) {
            return true;
        }

        return $this->subscriptions()->where('status', 'active')->count() < $this->max_subscriptions;
    }
}
