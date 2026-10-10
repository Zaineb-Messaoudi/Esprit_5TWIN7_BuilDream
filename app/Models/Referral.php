<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Referral made using a referral code.
 */
class Referral extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'referrals';

    protected $fillable = [
        'referral_code_id',
        'referrer_id',
        'referee_id',
        'status',
        'referrer_reward_earned',
        'referee_discount_applied',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'referrer_reward_earned' => 'decimal:2',
            'referee_discount_applied' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    /** The referral code used. */
    public function code(): BelongsTo
    {
        return $this->belongsTo(ReferralCode::class);
    }

    /** The user who referred (referrer). */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /** The user who was referred (referee). */
    public function referee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referee_id');
    }

    /** Rewards generated from this referral. */
    public function rewards(): HasMany
    {
        return $this->hasMany(ReferralReward::class);
    }

    /** Check if referral is completed. */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /** Mark referral as completed. */
    public function complete(decimal $rewardAmount, decimal $discountAmount): void
    {
        $this->update([
            'status' => 'completed',
            'referrer_reward_earned' => $rewardAmount,
            'referee_discount_applied' => $discountAmount,
            'completed_at' => now(),
        ]);

        // Create rewards
        ReferralReward::create([
            'referral_id' => $this->id,
            'user_id' => $this->referrer_id,
            'type' => 'referrer_reward',
            'amount' => $rewardAmount,
            'status' => 'available',
            'available_at' => now(),
        ]);
    }
}
