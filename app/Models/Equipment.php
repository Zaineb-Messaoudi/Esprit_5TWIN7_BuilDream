<?php

namespace App\Models;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

/**
 * A renewable-energy item published in the SolarShare catalogue.
 *
 * The owner is the authenticated user who manages the listing. Reservation,
 * rental, maintenance, and inspection modules reference this model through
 * its primary key.
 */
class Equipment extends Model
{
    use HasFactory, Searchable, SoftDeletes, Translatable;

    protected $translatable = ['name', 'description'];

    protected $table = 'equipment';

    protected $fillable = [
        'category_id', 'owner_id', 'name', 'description', 'image_url', 'brand', 'model',
        'price_per_day', 'condition', 'location', 'status', 'approval_status', 'reviewed_at',
        'latitude', 'longitude',
    ];

    protected function casts(): array
    {
        return ['price_per_day' => 'decimal:2', 'reviewed_at' => 'datetime', 'latitude' => 'decimal:8', 'longitude' => 'decimal:8'];
    }

    /**
     * Get the index name for the model.
     */
    public function searchableAs(): string
    {
        return 'equipment';
    }

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        $array = $this->toArray();

        // Add computed fields
        $array['owner_name'] = $this->owner?->name;
        $array['category_name'] = $this->category?->name;
        $array['status_label'] = $this->status;
        $array['approval_status_label'] = $this->approval_status;
        $array['condition_label'] = $this->condition;

        // Geospatial location for radius searches
        if ($this->latitude && $this->longitude) {
            $array['_geo'] = [
                'lat' => (float) $this->latitude,
                'lng' => (float) $this->longitude,
            ];
        }

        return $array;
    }

    /**
     * Configure index settings for database driver (no-op).
     */
    public static function getIndexSettings(): array
    {
        return [];
    }

    /**
     * Only index published and available equipment.
     */
    public function shouldBeSearchable(): bool
    {
        return $this->approval_status === 'published' && $this->status === 'available';
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

    public function images(): HasMany
    {
        return $this->hasMany(EquipmentImage::class)->orderBy('sort_order');
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'equipment_favorites')->withTimestamps();
    }

    /** Rentals created from this catalogue listing. */
    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    /** Reservations made against this catalogue listing. */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /** Maintenance interventions recorded for this listing. Equipment 1-N Maintenance. */
    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    /** Return/condition inspections recorded for this listing. Equipment 1-N Inspection. */
    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class);
    }

    /** Scope for published equipment only. */
    public function scopePublished($query)
    {
        return $query->where('approval_status', 'published')
            ->where('status', 'available');
    }

    /** Scope for nearby equipment (geospatial). */
    public function scopeNearby($query, float $lat, float $lng, float $radiusKm = 50)
    {
        return $query->whereRaw(
            'ST_Distance_Sphere(POINT(longitude, latitude), POINT(?, ?)) <= ?',
            [$lng, $lat, $radiusKm * 1000]
        );
    }
}
