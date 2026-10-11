<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Document sharing with permissions.
 */
class DocumentShare extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'document_shares';

    protected $fillable = [
        'document_id',
        'shared_by',
        'shared_with',
        'shared_email',
        'permission',
        'expires_at',
        'token',
        'accessed_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accessed_at' => 'datetime',
        ];
    }

    /** The document being shared. */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /** The user who shared the document. */
    public function sharedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_by');
    }

    /** The user the document was shared with. */
    public function sharedWith(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_with');
    }

    /** Check if share is active. */
    public function isActive(): bool
    {
        return ! $this->expires_at || $this->expires_at > now();
    }

    /** Check if share has expired. */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at < now();
    }

    /** Generate share token. */
    public function generateToken(): string
    {
        $token = Str::random(64);
        $this->update(['token' => $token]);

        return $token;
    }

    /** Get share URL. */
    public function getShareUrl(): string
    {
        return route('documents.shared', ['token' => $this->token]);
    }

    /** Record access. */
    public function recordAccess(): void
    {
        $this->update(['accessed_at' => now()]);
    }

    /** Scope for active shares. */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }
}
