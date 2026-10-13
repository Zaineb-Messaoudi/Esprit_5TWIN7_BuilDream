<?php

namespace App\Models;

use App\Enums\ExtensionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A request to prolong a rental (a rental can have several).
 * Table: rental_extensions
 */
class RentalExtension extends Model
{
    use HasFactory;

    /** Columns that can be mass assigned (create / update). */
    protected $fillable = [
        'rental_id',
        'requested_date',
        'old_end_date',
        'new_end_date',
        'additional_amount',
        'reason',
        'status',
    ];

    /**
     * Automatic type conversion:
     * - the three dates become Carbon objects
     * - additional_amount keeps 2 decimals
     * - status becomes an ExtensionStatus enum
     */
    protected function casts(): array
    {
        return [
            'requested_date' => 'date',
            'old_end_date' => 'date',
            'new_end_date' => 'date',
            'additional_amount' => 'decimal:2',
            'status' => ExtensionStatus::class,
        ];
    }

    /** The rental this request is about. RentalExtension N-1 Rental. */
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }
}
