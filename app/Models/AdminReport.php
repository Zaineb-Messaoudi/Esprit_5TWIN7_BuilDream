<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Admin report for user-generated content or behavior.
 */
class AdminReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'admin_reports';

    protected $fillable = [
        'reporter_id',
        'report_type',
        'subject_id',
        'subject_type',
        'reason',
        'description',
        'evidence',
        'status',
        'priority',
        'assigned_to',
        'resolved_by',
        'resolution',
        'resolved_at',
        'escalated_at',
    ];

    protected function casts(): array
    {
        return [
            'evidence' => 'array',
            'resolved_at' => 'datetime',
            'escalated_at' => 'datetime',
        ];
    }

    /** The user who filed the report. */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /** The admin assigned to this report. */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** The admin who resolved the report. */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /** Get the reported subject. */
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

    /** Check if report is open. */
    public function isOpen(): bool
    {
        return in_array($this->status, ['pending', 'under_review']);
    }

    /** Check if report is resolved. */
    public function isResolved(): bool
    {
        return in_array($this->status, ['resolved', 'dismissed']);
    }

    /** Assign report to admin. */
    public function assign(User $admin): void
    {
        $this->update([
            'assigned_to' => $admin->id,
            'status' => 'under_review',
        ]);
    }

    /** Resolve the report. */
    public function resolve(User $admin, string $resolution, string $status = 'resolved'): void
    {
        $this->update([
            'status' => $status,
            'resolved_by' => $admin->id,
            'resolution' => $resolution,
            'resolved_at' => now(),
        ]);
    }

    /** Escalate the report. */
    public function escalate(User $admin): void
    {
        $this->update([
            'status' => 'escalated',
            'escalated_at' => now(),
        ]);
    }

    /** Scope for open reports. */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['pending', 'under_review']);
    }

    /** Scope for high priority reports. */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'critical']);
    }
}
