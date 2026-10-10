<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Document with e-signature workflow.
 */
class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'documents';

    protected $fillable = [
        'template_id',
        'document_number',
        'title',
        'type',
        'status',
        'content',
        'variables',
        'metadata',
        'expires_at',
        'completed_at',
        'created_by',
        'owner_id',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'metadata' => 'array',
            'expires_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /** The template this document was created from. */
    public function template(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class);
    }

    /** User who created the document. */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Document owner. */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** Signature requests for this document. */
    public function signatures(): HasMany
    {
        return $this->hasMany(DocumentSignature::class);
    }

    /** Document versions. */
    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class)->orderByDesc('version');
    }

    /** Folders containing this document. */
    public function folders(): MorphMany
    {
        return $this->morphMany(DocumentFolderItem::class, 'document_id');
    }

    /** Document shares. */
    public function shares(): HasMany
    {
        return $this->hasMany(DocumentShare::class);
    }

    /** Audit trail. */
    public function auditTrail(): HasMany
    {
        return $this->hasMany(DocumentAuditTrail::class);
    }

    /** Check if document is in draft state. */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /** Check if document is pending signatures. */
    public function isPendingSignatures(): bool
    {
        return $this->status === 'pending_signatures';
    }

    /** Check if document is fully signed. */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /** Check if document has expired. */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at < now();
    }

    /** Get pending signatures. */
    public function pendingSignatures()
    {
        return $this->signatures()->where('status', 'pending');
    }

    /** Get completed signatures. */
    public function completedSignatures()
    {
        return $this->signatures()->where('status', 'signed');
    }

    /** Check if all required signatures are complete. */
    public function allSignaturesComplete(): bool
    {
        return $this->signatures()->where('status', '!=', 'signed')->doesntExist();
    }

    /** Get document statistics. */
    public function getStats(): array
    {
        $total = $this->signatures()->count();
        $signed = $this->signatures()->where('status', 'signed')->count();
        $pending = $this->signatures()->where('status', 'pending')->count();
        $declined = $this->signatures()->where('status', 'declined')->count();

        return [
            'total_signatures' => $total,
            'signed' => $signed,
            'pending' => $pending,
            'declined' => $declined,
            'completion_rate' => $total > 0 ? round(($signed / $total) * 100, 1) : 100,
        ];
    }

    /** Generate document number. */
    public static function generateNumber(): string
    {
        $year = now()->year;
        $lastNumber = static::whereYear('created_at', now()->year)
            ->max('document_number');

        if ($lastNumber && preg_match('/DOC-(\d{4})-(\d+)/', $lastNumber, $matches)) {
            $next = (int) $matches[2] + 1;
        } else {
            $next = 1;
        }

        return sprintf('DOC-%d-%04d', now()->year, $next);
    }

    /** Scope for active documents. */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['draft', 'pending_signatures', 'partially_signed', 'completed']);
    }

    /** Scope for pending signatures. */
    public function scopePendingSignatures($query)
    {
        return $query->where('status', 'pending_signatures');
    }
}
