<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
    use HasFactory, Searchable, SoftDeletes;

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
     * Configure index settings for Meilisearch.
     */
    public static function getIndexSettings(): array
    {
        return [
            'searchableAttributes' => [
                'name',
                'description',
                'brand',
                'model',
                'category_name',
                'owner_name',
                'location',
                'brand',
                'model',
            ],
            'filterableAttributes' => [
                'category_id',
                'owner_id',
                'status',
                'approval_status',
                'condition',
                'price_per_day',
                'power_watts',
                'capacity_wh',
            ],
            'sortableAttributes' => [
                'price_per_day',
                'created_at',
                'updated_at',
                'name',
            ],
            'rankingRules' => [
                'words',
                'typo',
                'proximity',
                'attribute',
                'sort',
                'exactness',
            ],
            'synonyms' => [
                'solar' => ['panel', 'panel', 'photovoltaic', 'pv'],
                'battery' => ['storage', 'accumulator', 'power bank'],
                'wind' => ['turbine', 'generator'],
            ],
        ];
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
