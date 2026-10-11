<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Affiliate program configuration.
 */
class AffiliateProgram extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'affiliate_programs';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'base_commission_rate',
        'tier_1_commission',
        'tier_2_commission',
        'cookie_duration_days',
        'min_payout_threshold',
        'is_active',
        'terms',
    ];

    protected function casts(): array
    {
        return [
            'base_commission_rate' => 'decimal:2',
            'tier_1_commission' => 'decimal:2',
            'tier_2_commission' => 'decimal:2',
            'is_active' => 'boolean',
            'terms' => 'array',
        ];
    }

    /** Affiliates in this program. */
    public function affiliates(): HasMany
    {
        return $this->hasMany(Affiliate::class);
    }

    /** Calculate commission for a given amount. */
    public function calculateCommission(float $amount, int $tier = 0): float
    {
        $rate = match ($tier) {
            0 => $this->base_commission_rate,
            1 => $this->tier_1_commission ?? 0,
            2 => $this->tier_2_commission ?? 0,
            default => 0,
        };

        return round($amount * ($rate / 100), 2);
    }

    /** Scope for active programs. */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
