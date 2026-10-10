<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Rental protection (insurance) purchased for a rental.
 */
class RentalProtection extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rental_protections';

    protected $fillable = [
        'rental_id',
        'protection_plan_id',
        'purchased_by',
        'premium_paid',
        'starts_at',
        'expires_at',
        'status',
        'claims_count',
        'claim_history',
    ];

    protected function casts(): array
    {
        return [
            'premium_paid' => 'decimal:2',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'claim_history' => 'array',
        ];
    }

    /** The rental this protection is for. */
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    /** The protection plan. */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(ProtectionPlan::class);
    }

    /** The user who purchased the protection. */
    public function purchaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'purchased_by');
    }

    /** Claims filed against this protection. */
    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }

    /** Check if protection is active. */
    public function isActive(): bool
    {
        return $this->status === 'active'
            && $this->starts_at <= now()
            && $this->expires_at >= now();
    }

    /** Check if a claim can be filed. */
    public function canFileClaim(): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        $plan = $this->plan;

        return $this->claims_count < $plan->max_claim_count;
    }

    /** Get remaining claim count. */
    public function remainingClaims(): int
    {
        return max(0, $this->plan->max_claim_count - $this->claims_count);
    }

    /** Scope for active protections. */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where('expires_at', '>=', now());
    }
}
