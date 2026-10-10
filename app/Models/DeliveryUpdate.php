<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Status update for a delivery tracking.
 */
class DeliveryUpdate extends Model
{
    use HasFactory;

    protected $table = 'delivery_updates';

    protected $fillable = [
        'delivery_id',
        'status',
        'latitude',
        'longitude',
        'location_name',
        'notes',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    /** The delivery this update belongs to. */
    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    /** The user who made this update. */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /** Check if this update includes geolocation. */
    public function hasLocation(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }
}
