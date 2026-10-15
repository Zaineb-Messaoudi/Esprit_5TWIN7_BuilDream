<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Stripe Connect account for marketplace sellers (owners).
 */
class StripeAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'stripe_accounts';

    protected $fillable = [
        'user_id',
        'stripe_account_id',
        'status',
        'charges_enabled',
        'payouts_enabled',
        'details_submitted',
        'requirements',
        'capabilities',
        'business_type',
        'country',
        'default_currency',
        'business_profile',
        'settings',
        'onboarded_at',
    ];

    protected function casts(): array
    {
        return [
            'charges_enabled' => 'boolean',
            'payouts_enabled' => 'boolean',
            'details_submitted' => 'boolean',
            'requirements' => 'array',
            'capabilities' => 'array',
            'business_profile' => 'array',
            'settings' => 'array',
            'onboarded_at' => 'datetime',
        ];
    }

    /** The user who owns this Stripe account. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Check if account is fully onboarded. */
    public function isOnboarded(): bool
    {
        return $this->status === 'active'
            && $this->charges_enabled
            && $this->payouts_enabled
            && $this->details_submitted;
    }

    /** Check if account can receive payments. */
    public function canReceivePayments(): bool
    {
        return $this->status === 'active' && $this->charges_enabled;
    }

    /** Check if account can make payouts. */
    public function canPayout(): bool
    {
        return $this->status === 'active' && $this->payouts_enabled;
    }

    /** Get onboarding status summary. */
    public function getOnboardingStatus(): array
    {
        $requirements = $this->requirements ?? [];

        return [
            'details_submitted' => $this->details_submitted,
            'charges_enabled' => $this->charges_enabled,
            'payouts_enabled' => $this->payouts_enabled,
            'currently_due' => $requirements['currently_due'] ?? [],
            'eventually_due' => $requirements['eventually_due'] ?? [],
            'past_due' => $requirements['past_due'] ?? [],
            'pending_verification' => $requirements['pending_verification'] ?? [],
        ];
    }
}
