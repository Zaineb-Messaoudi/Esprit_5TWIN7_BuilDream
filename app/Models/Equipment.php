<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A renewable-energy item published in the SolarShare catalogue.
 *
 * The owner is the authenticated user who manages the listing. Reservation,
 * rental, maintenance, and inspection modules reference this model through
 * its primary key.
 */
class Equipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'equipment';

    protected $fillable = [
        'category_id', 'owner_id', 'name', 'description', 'image_url', 'brand', 'model',
        'price_per_day', 'condition', 'location', 'status', 'approval_status', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return ['price_per_day' => 'decimal:2', 'reviewed_at' => 'datetime'];
    }

    /** The catalogue category for this listing. */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** The user who owns and publishes this listing. */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** Technical energy characteristics for this listing. */
    public function energyProfile(): HasOne
    {
        return $this->hasOne(EnergyProfile::class);
    }
}
