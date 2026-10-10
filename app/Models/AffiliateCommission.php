<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Commission earned by an affiliate.
 */
class AffiliateCommission extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'affiliate_commissions';

    protected $fillable = [
        'affiliate_id',
        'referral_id',
        'payment_id',
        'commission_number',
        'amount',
        'rate_applied',
        'status',
        'approved_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'rate_applied' => 'decimal:2',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    /** The affiliate who earned this commission. */
    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    /** The referral that generated this commission (if any). */
    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
    }

    /** The payment that generated this commission (if any). */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /** Check if commission is pending. */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /** Check if commission is approved. */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /** Check if commission is paid. */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /** Approve the commission. */
    public function approve(): void
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // Update affiliate's pending earnings
        $this->affiliate->increment('pending_earnings', $this->amount);
    }

    /** Mark commission as paid. */
    public function markPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Update affiliate's earnings
        $this->affiliate->decrement('pending_earnings', $this->amount);
        $this->affiliate->increment('paid_earnings', $this->amount);
        $this->affiliate->increment('total_earnings', $this->amount);
    }
}
