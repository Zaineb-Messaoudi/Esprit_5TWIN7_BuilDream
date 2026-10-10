<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Signature request/record for a document.
 */
class DocumentSignature extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'document_signatures';

    protected $fillable = [
        'document_id',
        'signer_id',
        'signer_email',
        'signer_name',
        'signer_role',
        'status',
        'signature_data',
        'signature_hash',
        'signature_metadata',
        'decline_reason',
        'viewed_at',
        'signed_at',
        'expires_at',
        'token',
    ];

    protected function casts(): array
    {
        return [
            'signature_metadata' => 'array',
            'viewed_at' => 'datetime',
            'signed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /** The document being signed. */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /** The user who signed (if internal). */
    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signer_id');
    }

    /** Check if signature is pending. */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /** Check if signature is completed. */
    public function isSigned(): bool
    {
        return $this->status === 'signed';
    }

    /** Check if signature was declined. */
    public function isDeclined(): bool
    {
        return $this->status === 'declined';
    }

    /** Check if signature has expired. */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at < now();
    }

    /** Check if signature can be signed. */
    public function canBeSigned(): bool
    {
        return $this->status === 'pending' && ! $this->isExpired();
    }

    /** Generate signing token. */
    public function generateToken(): string
    {
        $token = Str::random(64);
        $this->update(['token' => $token]);

        return $token;
    }

    /** Get signing URL. */
    public function getSigningUrl(): string
    {
        return route('documents.sign', ['token' => $this->token]);
    }

    /** Mark as viewed. */
    public function markViewed(array $metadata = []): void
    {
        if ($this->status === 'pending') {
            $this->update([
                'status' => 'viewed',
                'viewed_at' => now(),
                'signature_metadata' => array_merge($this->signature_metadata ?? [], $metadata),
            ]);
        }
    }

    /** Sign the document. */
    public function sign(string $signatureData, array $metadata = []): bool
    {
        if (! $this->canBeSigned()) {
            return false;
        }

        $this->update([
            'status' => 'signed',
            'signature_data' => $signatureData,
            'signature_hash' => hash('sha256', $signatureData),
            'signed_at' => now(),
            'signature_metadata' => array_merge($this->signature_metadata ?? [], $metadata),
        ]);

        // Check if document is now complete
        $document = $this->document;
        if ($document->allSignaturesComplete()) {
            $document->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        } elseif ($document->status === 'pending_signatures') {
            $document->update(['status' => 'partially_signed']);
        }

        return true;
    }

    /** Decline to sign. */
    public function decline(string $reason): void
    {
        $this->update([
            'status' => 'declined',
            'decline_reason' => $reason,
        ]);

        // Update document status
        $this->document->update(['status' => 'declined']);
    }
}
