<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Reward earned from a referral.
 */
class ReferralReward extends Model
{
    use HasFactory;

    protected $table = 'referral_rewards';

    protected $fillable = [
        'referral_id',
        'user_id',
        'type',
        'amount',
        'status',
        'available_at',
        'claimed_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'available_at' => 'datetime',
            'claimed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /** The referral that generated this reward. */
    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
    }

    /** The user who earned the reward. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Check if reward is claimable. */
    public function isClaimable(): bool
    {
        return $this->status === 'available'
            && $this->available_at <= now()
            && (! $this->expires_at || $this->expires_at > now());
    }

    /** Claim the reward. */
    public function claim(): bool
    {
        if (! $this->isClaimable()) {
            return false;
        }

        $this->update([
            'status' => 'claimed',
            'claimed_at' => now(),
        ]);

        return true;
    }
}
