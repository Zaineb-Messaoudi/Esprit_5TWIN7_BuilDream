<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Dispute filed against a review.
 */
class ReviewDispute extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'review_disputes';

    protected $fillable = [
        'review_id',
        'initiator_id',
        'reason',
        'description',
        'status',
        'resolved_by',
        'resolution',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    /** The review being disputed. */
    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    /** The user who initiated the dispute. */
    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiator_id');
    }

    /** The admin who resolved the dispute. */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /** Check if dispute is open. */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /** Check if dispute is resolved. */
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    /** Scope for open disputes. */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
}
