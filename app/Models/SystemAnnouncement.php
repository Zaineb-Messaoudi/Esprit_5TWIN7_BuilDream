<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * System-wide announcements.
 */
class SystemAnnouncement extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'system_announcements';

    protected $fillable = [
        'title',
        'content',
        'type',
        'audience',
        'target_users',
        'priority',
        'is_published',
        'send_email',
        'send_push',
        'published_at',
        'expires_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'target_users' => 'array',
            'is_published' => 'boolean',
            'send_email' => 'boolean',
            'send_push' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /** The admin who created the announcement. */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Check if announcement is currently active. */
    public function isActive(): bool
    {
        return $this->is_published
            && (! $this->expires_at || $this->expires_at >= now());
    }

    /** Check if announcement is for a specific user. */
    public function isForUser(User $user): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        return match ($this->audience) {
            'all' => true,
            'owners' => $user->isOwner(),
            'renters' => $user->isBuyer(),
            'admins' => $user->isAdmin(),
            'specific' => $this->target_users && in_array($user->id, $this->target_users),
            default => false,
        };
    }

    /** Publish the announcement. */
    public function publish(): void
    {
        $this->update([
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    /** Unpublish the announcement. */
    public function unpublish(): void
    {
        $this->update([
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    /** Scope for active announcements. */
    public function scopeActive($query)
    {
        return $query->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            });
    }

    /** Scope for announcements for a specific user. */
    public function scopeForUser($query, User $user)
    {
        return $query->active()->get()->filter(fn ($a) => $a->isForUser($user));
    }
}
