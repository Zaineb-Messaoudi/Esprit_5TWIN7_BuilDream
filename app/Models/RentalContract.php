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
        'deposit_status',
        'deposit_held_at',
        'deposit_released_at',
        'deposit_notes',
        'contract_status',
    ];

    /**
     * Automatic type conversion:
     * - signed_at becomes a Carbon date-time (or null if not signed yet)
     * - deposit_amount keeps 2 decimals
     * - contract_status becomes a ContractStatus enum
     * - deposit_status becomes a DepositStatus enum
     */
    protected function casts(): array
    {
        return [
            'signed_at' => 'datetime',
            'deposit_amount' => 'decimal:2',
            'deposit_held_at' => 'datetime',
            'deposit_released_at' => 'datetime',
            'contract_status' => ContractStatus::class,
            'deposit_status' => \App\Enums\DepositStatus::class,
        ];
    }

    /** The rental this contract belongs to. RentalContract N-1 Rental (in practice 1-1). */
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    /** Hold the deposit when rental starts. */
    public function holdDeposit(?string $notes = null): void
    {
        $this->update([
            'deposit_status' => DepositStatus::HELD,
            'deposit_held_at' => now(),
            'deposit_notes' => $notes,
        ]);
    }

    /** Release the deposit when equipment returns undamaged. */
    public function releaseDeposit(?string $notes = null): void
    {
        $this->update([
            'deposit_status' => DepositStatus::RELEASED,
            'deposit_released_at' => now(),
            'deposit_notes' => $notes,
        ]);
    }

    /** Forfeit the deposit (partial or full) due to damage. */
    public function forfeitDeposit(?string $notes = null): void
    {
        $this->update([
            'deposit_status' => DepositStatus::FORFEITED,
            'deposit_released_at' => now(),
            'deposit_notes' => $notes,
        ]);
    }

    /** Check if deposit can be held. */
    public function canHoldDeposit(): bool
    {
        return $this->deposit_status === DepositStatus::PENDING;
    }

    /** Check if deposit can be released. */
    public function canReleaseDeposit(): bool
    {
        return $this->deposit_status === DepositStatus::HELD;
    }

    /** Check if deposit can be forfeited. */
    public function canForfeitDeposit(): bool
    {
        return in_array($this->deposit_status, [DepositStatus::PENDING, DepositStatus::HELD]);
    }
}
