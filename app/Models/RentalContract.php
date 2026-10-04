<?php

namespace App\Models;

use App\Enums\ContractStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The contract signed for a rental (exactly one per rental).
 * Table: rental_contracts
 */
class RentalContract extends Model
{
    use HasFactory;

    /** Columns that can be mass assigned (create / update). */
    protected $fillable = [
        'rental_id',
        'contract_number',
        'signed_at',
        'terms',
        'deposit_amount',
        'contract_status',
    ];

    /**
     * Automatic type conversion:
     * - signed_at becomes a Carbon date-time (or null if not signed yet)
     * - deposit_amount keeps 2 decimals
     * - contract_status becomes a ContractStatus enum
     */
    protected function casts(): array
    {
        return [
            'signed_at'       => 'datetime',
            'deposit_amount'  => 'decimal:2',
            'contract_status' => ContractStatus::class,
        ];
    }

    /** The rental this contract belongs to. RentalContract N-1 Rental (in practice 1-1). */
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }
}