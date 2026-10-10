<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Affiliate (partner) in an affiliate program.
 */
class Affiliate extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'affiliates';

    protected $fillable = [
        'affiliate_program_id',
        'user_id',
        'referral_code',
        'parent_affiliate_id',
        'tier',
        'commission_rate',
        'status',
        'total_earnings',
        'pending_earnings',
        'paid_earnings',
        'approved_at',
        'suspended_at',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'total_earnings' => 'decimal:2',
            'pending_earnings' => 'decimal:2',
            'paid_earnings' => 'decimal:2',
            'approved_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }

    /** The affiliate program. */
    public function program(): BelongsTo
    {
        return $this->belongsTo(AffiliateProgram::class);
    }

    /** The user who is the affiliate. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Parent affiliate (for multi-tier). */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class, 'parent_affiliate_id');
    }

    /** Child affiliates (sub-affiliates). */
    public function children(): HasMany
    {
        return $this->hasMany(Affiliate::class, 'parent_affiliate_id');
    }

    /** Commissions earned by this affiliate. */
    public function commissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class);
    }

    /** Check if affiliate is active. */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /** Get effective commission rate. */
    public function getEffectiveRate(): float
    {
        return $this->commission_rate > 0 ? $this->commission_rate : $this->program->base_commission_rate;
    }

    /** Scope for active affiliates. */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
