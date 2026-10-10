<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Organization member with role and permissions.
 */
class OrganizationMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'organization_members';

    protected $fillable = [
        'organization_id',
        'user_id',
        'role',
        'permissions',
        'joined_at',
        'invited_at',
        'accepted_at',
        'invite_token',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'joined_at' => 'datetime',
            'invited_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    /** The organization. */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** The user. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Check if member is owner. */
    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    /** Check if member is admin. */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['owner', 'admin']);
    }

    /** Check if member can manage teams. */
    public function canManageTeams(): bool
    {
        return in_array($this->role, ['owner', 'admin', 'manager']);
    }

    /** Check if member can manage billing. */
    public function canManageBilling(): bool
    {
        return in_array($this->role, ['owner', 'admin']);
    }

    /** Check if member can invite others. */
    public function canInvite(): bool
    {
        return in_array($this->role, ['owner', 'admin', 'manager']);
    }

    /** Check if member has accepted invitation. */
    public function hasAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    /** Check if member has custom permission. */
    public function hasPermission(string $permission): bool
    {
        if ($this->isOwner()) {
            return true;
        }

        $permissions = $this->permissions ?? [];

        return in_array($permission, $permissions);
    }

    /** Scope for accepted members. */
    public function scopeAccepted($query)
    {
        return $query->whereNotNull('accepted_at');
    }

    /** Scope by role. */
    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }
}
