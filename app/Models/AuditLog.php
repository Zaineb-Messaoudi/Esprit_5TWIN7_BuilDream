<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Audit log for GDPR compliance and security monitoring.
 */
class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'event_type',
        'event_action',
        'subject_type',
        'subject_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
        'method',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    /** The user who performed the action. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Get the subject model. */
    public function getSubject(): ?Model
    {
        if (! $this->subject_type || ! $this->subject_id) {
            return null;
        }

        try {
            return $this->subject_type::find($this->subject_id);
        } catch (\Exception $e) {
            return null;
        }
    }

    /** Scope for recent logs. */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /** Scope for specific event type. */
    public function scopeEventType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /** Scope for specific user. */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
