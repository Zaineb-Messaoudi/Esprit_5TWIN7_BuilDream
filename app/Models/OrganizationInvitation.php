<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Organization invitation.
 */
class OrganizationInvitation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'organization_invitations';

    protected $fillable = [
        'organization_id',
        'invited_by',
        'email',
        'role',
        'team_ids',
        'token',
        'status',
        'expires_at',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'team_ids' => 'array',
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    /** The organization. */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** The user who sent the invitation. */
    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /** Check if invitation is pending. */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /** Check if invitation is expired. */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at < now();
    }

    /** Check if invitation can be accepted. */
    public function canBeAccepted(): bool
    {
        return $this->status === 'pending' && ! $this->isExpired();
    }

    /** Generate invitation token. */
    public function generateToken(): string
    {
        $token = Str::random(64);
        $this->update(['token' => $token]);

        return $token;
    }

    /** Accept the invitation. */
    public function accept(User $user): void
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        // Create organization member
        $this->organization->members()->create([
            'user_id' => $user->id,
            'role' => $this->role,
            'accepted_at' => now(),
        ]);

        // Add to teams if specified
        if ($this->team_ids) {
            foreach ($this->team_ids as $teamId) {
                $team = Team::find($teamId);
                if ($team && $team->organization_id === $this->organization_id) {
                    $team->members()->create([
                        'user_id' => $user->id,
                        'role' => 'member',
                    ]);
                }
            }
        }
    }

    /** Revoke the invitation. */
    public function revoke(): void
    {
        $this->update(['status' => 'revoked']);
    }

    /** Scope for pending invitations. */
    public function scopePending($query)
    {
        return $query->where('status', 'pending')->where('expires_at', '>', now());
    }
}
