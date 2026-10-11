<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Protection plan (insurance product) for rentals.
 */
class ProtectionPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'protection_plans';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'tier',
        'price_per_day',
        'coverage_amount',
        'deductible',
        'covered_risks',
        'exclusions',
        'max_claim_count',
        'waiting_period_days',
        'is_active',
        'terms_and_conditions',
    ];

    protected function casts(): array
    {
        return [
            'price_per_day' => 'decimal:2',
            'coverage_amount' => 'decimal:2',
            'deductible' => 'decimal:2',
            'covered_risks' => 'array',
            'exclusions' => 'array',
            'is_active' => 'boolean',
            'terms_and_conditions' => 'array',
        ];
    }

    /** Rentals that have purchased this plan. */
    public function rentalProtections(): HasMany
    {
        return $this->hasMany(RentalProtection::class);
    }

    /** Get the formatted tier label. */
    public function getTierLabelAttribute(): string
    {
        return ucfirst($this->tier);
    }

    /** Check if a risk is covered. */
    public function coversRisk(string $risk): bool
    {
        return in_array($risk, $this->covered_risks ?? []);
    }

    /** Scope for active plans. */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Scope for plans by tier. */
    public function scopeTier($query, string $tier)
    {
        return $query->where('tier', $tier);
    }
}
