<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Team member with role.
 */
class TeamMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'team_members';

    protected $fillable = [
        'team_id',
        'user_id',
        'role',
        'permissions',
        'joined_at',
        'left_at',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
        ];
    }

    /** The team. */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /** The user. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Check if member is lead. */
    public function isLead(): bool
    {
        return $this->role === 'lead';
    }

    /** Check if member is admin. */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['lead', 'admin']);
    }

    /** Check if member is active. */
    public function isActive(): bool
    {
        return $this->left_at === null;
    }

    /** Check if member has permission. */
    public function hasPermission(string $permission): bool
    {
        if ($this->isLead()) {
            return true;
        }

        $permissions = $this->permissions ?? [];

        return in_array($permission, $permissions);
    }

    /** Leave the team. */
    public function leave(): void
    {
        $this->update(['left_at' => now()]);
    }

    /** Scope for active members. */
    public function scopeActive($query)
    {
        return $query->whereNull('left_at');
    }

    /** Scope by role. */
    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }
}
