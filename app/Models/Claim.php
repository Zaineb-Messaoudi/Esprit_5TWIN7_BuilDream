<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Insurance claim filed against a rental protection.
 */
class Claim extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'claims';

    protected $fillable = [
        'rental_protection_id',
        'reported_by',
        'claim_number',
        'type',
        'description',
        'estimated_cost',
        'approved_amount',
        'status',
        'evidence_photos',
        'documents',
        'adjuster_notes',
        'assessed_by',
        'submitted_at',
        'assessed_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'estimated_cost' => 'decimal:2',
            'approved_amount' => 'decimal:2',
            'evidence_photos' => 'array',
            'documents' => 'array',
            'submitted_at' => 'datetime',
            'assessed_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    /** The rental protection this claim is against. */
    public function protection(): BelongsTo
    {
        return $this->belongsTo(RentalProtection::class, 'rental_protection_id');
    }

    /** The user who reported the claim. */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /** The admin who assessed the claim. */
    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }

    /** Check if claim is pending. */
    public function isPending(): bool
    {
        return $this->status === 'submitted';
    }

    /** Check if claim is approved. */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /** Check if claim is paid. */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /** Scope for pending claims. */
    public function scopePending($query)
    {
        return $query->where('status', 'submitted');
    }

    /** Scope for claims by status. */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
