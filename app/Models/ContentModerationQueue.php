<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Content moderation queue for automated and manual review.
 */
class ContentModerationQueue extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'content_moderation_queue';

    protected $fillable = [
        'content_type',
        'content_id',
        'content_hash',
        'status',
        'flagged_categories',
        'confidence_score',
        'reviewed_by',
        'review_notes',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'flagged_categories' => 'array',
            'confidence_score' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    /** The moderator who reviewed this content. */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** Get the content being moderated. */
    public function getContent(): ?Model
    {
        if (! $this->content_type || ! $this->content_id) {
            return null;
        }

        try {
            return $this->content_type::find($this->content_id);
        } catch (\Exception $e) {
            return null;
        }
    }

    /** Check if content is pending review. */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /** Check if content is approved. */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /** Check if content is rejected. */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /** Approve the content. */
    public function approve(User $moderator, ?string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'reviewed_by' => $moderator->id,
            'review_notes' => $notes,
            'reviewed_at' => now(),
        ]);
    }

    /** Reject the content. */
    public function reject(User $moderator, ?string $notes = null): void
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $moderator->id,
            'review_notes' => $notes,
            'reviewed_at' => now(),
        ]);
    }

    /** Flag for manual review. */
    public function flag(User $moderator, array $categories, ?string $notes = null): void
    {
        $this->update([
            'status' => 'flagged',
            'flagged_categories' => $categories,
            'reviewed_by' => $moderator->id,
            'review_notes' => $notes,
            'reviewed_at' => now(),
        ]);
    }

    /** Scope for pending items. */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /** Scope for flagged items. */
    public function scopeFlagged($query)
    {
        return $query->where('status', 'flagged');
    }
}
