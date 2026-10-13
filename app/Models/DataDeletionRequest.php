<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * User request for data deletion (GDPR Article 17).
 */
class DataDeletionRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'data_deletion_requests';

    protected $fillable = [
        'user_id',
        'status',
        'reason',
        'delete_categories',
        'anonymize_instead',
        'verification_token',
        'verified_at',
        'completed_at',
        'rejection_reason',
        'scheduled_at',
    ];

    protected function casts(): array
    {
        return [
            'delete_categories' => 'array',
            'anonymize_instead' => 'boolean',
            'verified_at' => 'datetime',
            'completed_at' => 'datetime',
            'scheduled_at' => 'datetime',
        ];
    }

    /** The user requesting deletion. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Generate verification token. */
    public function generateVerificationToken(): string
    {
        $token = Str::random(64);
        $this->update([
            'verification_token' => hash('sha256', $token),
        ]);

        return $token;
    }

    /** Verify the token. */
    public function verifyToken(string $token): bool
    {
        if (! $this->verification_token) {
            return false;
        }

        if (hash_equals($this->verification_token, hash('sha256', $token))) {
            $this->update([
                'status' => 'verified',
                'verified_at' => now(),
            ]);

            return true;
        }

        return false;
    }

    /** Check if request is pending verification. */
    public function isPendingVerification(): bool
    {
        return $this->status === 'pending' || $this->status === 'verification_sent';
    }

    /** Check if request is verified. */
    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    /** Approve and schedule deletion. */
    public function approve(): void
    {
        $this->update([
            'status' => 'processing',
            'scheduled_at' => now()->addDays(30), // 30-day grace period
        ]);
    }

    /** Reject the request. */
    public function reject(string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);
    }

    /** Scope for pending requests. */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'verification_sent', 'verified', 'processing']);
    }
}
