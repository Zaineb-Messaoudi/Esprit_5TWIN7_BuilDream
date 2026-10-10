<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Escrow account for holding funds during rental.
 */
class EscrowAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'escrow_accounts';

    protected $fillable = [
        'rental_id',
        'payer_id',
        'payee_id',
        'stripe_payment_intent_id',
        'stripe_transfer_id',
        'amount',
        'platform_fee',
        'stripe_fee',
        'currency',
        'status',
        'stripe_charge_id',
        'stripe_refund_id',
        'metadata',
        'authorized_at',
        'captured_at',
        'released_at',
        'refunded_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'platform_fee' => 'decimal:2',
            'stripe_fee' => 'decimal:2',
            'metadata' => 'array',
            'authorized_at' => 'datetime',
            'captured_at' => 'datetime',
            'released_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    /** The rental this escrow is for. */
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    /** The user who paid (renter). */
    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    /** The user who receives payment (owner). */
    public function payee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payee_id');
    }

    /** Check if escrow is active (held). */
    public function isHeld(): bool
    {
        return $this->status === 'held';
    }

    /** Check if escrow is released. */
    public function isReleased(): bool
    {
        return $this->status === 'released';
    }

    /** Check if escrow is refunded. */
    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    /** Get net amount to payee (after fees). */
    public function getNetAmountAttribute(): float
    {
        return round($this->amount - $this->platform_fee - $this->stripe_fee, 2);
    }

    /** Check if escrow can be released. */
    public function canBeReleased(): bool
    {
        return $this->status === 'held' || $this->status === 'captured';
    }

    /** Check if escrow can be refunded. */
    public function canBeRefunded(): bool
    {
        return in_array($this->status, ['authorized', 'captured', 'held']);
    }

    /** Check if escrow is in a terminal state. */
    public function isTerminal(): bool
    {
        return in_array($this->status, ['released', 'refunded', 'cancelled', 'disputed']);
    }
}
