<?php

namespace App\Models;

use App\Enums\RentalStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * A rental: an equipment lent to a user for a period of time.
 * Table: rentals
 */
class Rental extends Model
{
    use HasFactory;

    /**
     * Columns that can be filled with Rental::create([...]) or $rental->update([...]).
     * (Mass assignment protection: any column not listed here is ignored.)
     */
    protected $fillable = [
        'reference',
        'equipment_id',
        'user_id',
        'reservation_id',
        'start_date',
        'end_date',
        'total_amount',
        'status',
    ];

    /**
     * Automatic type conversion when reading/writing columns.
     * - dates become Carbon objects (so we can call ->format(), ->diffInDays()...)
     * - total_amount is always handled with 2 decimals
     * - status becomes a RentalStatus enum
     */
    protected function casts(): array
    {
        return [
            'start_date'   => 'date',
            'end_date'     => 'date',
            'total_amount' => 'decimal:2',
            'status'       => RentalStatus::class,
        ];
    }

    // ------------------------------------------------------------------
    // Relations
    // ------------------------------------------------------------------

    /** The renter (User). Rental N-1 User, through user_id. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The rented equipment. Equipment 1-N Rental, through equipment_id.
     * The Equipment model is written by Student 1: this method only works once
     * their model is merged into the project.
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * The reservation this rental comes from. Written by Student 4:
     * same remark, it works once the Reservation model is merged.
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /** The contract of this rental. Rental 1-1 RentalContract. */
    public function contract(): HasOne
    {
        return $this->hasOne(RentalContract::class);
    }

    /** All extension requests of this rental. Rental 1-N RentalExtension. */
    public function extensions(): HasMany
    {
        return $this->hasMany(RentalExtension::class);
    }

    // ------------------------------------------------------------------
    // Accessors
    // ------------------------------------------------------------------

    /**
     * Text to display for the equipment: $rental->equipment_label
     * - once Student 1's model is merged and the equipment is found: its name
     * - before that (or if the equipment is missing): "Equipment #id"
     */
    public function getEquipmentLabelAttribute(): string
    {
        if (class_exists(Equipment::class) && $this->equipment) {
            return $this->equipment->name;
        }

        return 'Equipment #' . $this->equipment_id;
    }
}