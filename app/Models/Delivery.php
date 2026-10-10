<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Delivery tracking for equipment rentals.
 */
class Delivery extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'deliveries';

    protected $fillable = [
        'rental_id',
        'equipment_id',
        'owner_id',
        'renter_id',
        'type',
        'status',
        'tracking_number',
        'carrier',
        'pickup_address',
        'dropoff_address',
        'scheduled_pickup_at',
        'actual_pickup_at',
        'scheduled_dropoff_at',
        'actual_dropoff_at',
        'dimensions',
        'special_instructions',
        'delivery_fee',
        'fee_paid_by',
        'proof_of_pickup_url',
        'proof_of_dropoff_url',
        'issue_notes',
    ];

    protected function casts(): array
    {
        return [
            'pickup_address' => 'array',
            'dropoff_address' => 'array',
            'dimensions' => 'array',
            'delivery_fee' => 'decimal:2',
            'scheduled_pickup_at' => 'datetime',
            'actual_pickup_at' => 'datetime',
            'scheduled_dropoff_at' => 'datetime',
            'actual_dropoff_at' => 'datetime',
        ];
    }

    /** The rental this delivery is for. */
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    /** The equipment being delivered. */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /** The equipment owner. */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** The renter (buyer). */
    public function renter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'renter_id');
    }

    /** Status updates for this delivery. */
    public function updates(): HasMany
    {
        return $this->hasMany(DeliveryUpdate::class)->latest();
    }

    /** Check if delivery is active (in transit or scheduled). */
    public function isActive(): bool
    {
        return in_array($this->status, ['scheduled', 'in_transit']);
    }

    /** Check if delivery is completed. */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /** Get the next scheduled action. */
    public function nextAction(): ?string
    {
        if ($this->status === 'scheduled' && $this->scheduled_pickup_at) {
            return 'pickup';
        }
        if ($this->status === 'in_transit' && $this->type !== 'pickup' && $this->scheduled_dropoff_at) {
            return 'dropoff';
        }

        return null;
    }

    /** Scope for active deliveries. */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['scheduled', 'in_transit']);
    }

    /** Scope for deliveries needing attention. */
    public function scopeNeedingAttention($query)
    {
        return $query->where('status', 'issue')
            ->orWhere(function ($q) {
                $q->where('status', 'scheduled')
                    ->where('scheduled_pickup_at', '<', now());
            });
    }
}
