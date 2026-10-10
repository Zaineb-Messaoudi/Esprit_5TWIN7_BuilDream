<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Seasonal pricing adjustments for equipment.
 */
class SeasonalPricing extends Model
{
    use HasFactory;

    protected $table = 'seasonal_pricing';

    protected $fillable = [
        'equipment_id',
        'name',
        'starts_at',
        'ends_at',
        'price_multiplier',
        'applicable_days',
        'is_active',
        'priority',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'price_multiplier' => 'decimal:2',
            'applicable_days' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /** The equipment this pricing applies to. */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /** Check if pricing is currently active. */
    public function isActive(): bool
    {
        return $this->is_active
            && $this->starts_at <= now()
            && $this->ends_at >= now();
    }

    /** Check if pricing applies to a specific date. */
    public function appliesToDate(\Illuminate\Support\Carbon $date): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        if ($date->lt($this->starts_at) || $date->gt($this->ends_at)) {
            return false;
        }

        if ($this->applicable_days && ! in_array(strtolower($date->format('l')), $this->applicable_days)) {
            return false;
        }

        return true;
    }

    /** Calculate adjusted price for a base price. */
    public function adjustPrice(float $basePrice): float
    {
        return round($basePrice * $this->price_multiplier, 2);
    }

    /** Scope for currently active seasonal pricing. */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->orderByDesc('priority');
    }

    /** Get applicable seasonal pricing for equipment on a date. */
    public static function getApplicableFor(Equipment $equipment, \Illuminate\Support\Carbon $date): ?self
    {
        return self::active()
            ->where('equipment_id', $equipment->id)
            ->get()
            ->first(fn ($p) => $p->appliesToDate($date));
    }
}
