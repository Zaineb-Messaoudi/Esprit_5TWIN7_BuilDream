<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Audit trail for document actions.
 */
class DocumentAuditTrail extends Model
{
    use HasFactory;

    protected $table = 'document_audit_trails';

    protected $fillable = [
        'document_id',
        'user_id',
        'action',
        'details',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    /** The document this audit trail belongs to. */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /** The user who performed the action. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Get human-readable action label. */
    public function getActionLabel(): string
    {
        return match ($this->action) {
            'created' => 'Created',
            'updated' => 'Updated',
            'viewed' => 'Viewed',
            'downloaded' => 'Downloaded',
            'signed' => 'Signed',
            'shared' => 'Shared',
            'revoked' => 'Access Revoked',
            'deleted' => 'Deleted',
            'restored' => 'Restored',
            'version_created' => 'Version Created',
            'signature_requested' => 'Signature Requested',
            'signature_signed' => 'Signature Completed',
            'signature_declined' => 'Signature Declined',
            'shared' => 'Shared',
            'access_revoked' => 'Access Revoked',
            default => ucfirst($this->action),
        };
    }

    /** Scope for recent audit entries. */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
