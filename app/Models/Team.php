<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Team within an organization.
 */
class Team extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'teams';

    protected $fillable = [
        'organization_id',
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'is_private',
        'lead_id',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    /** The organization this team belongs to. */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** Team lead. */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_id');
    }

    /** Team members. */
    public function members(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    /** Invitations to join team. */
    public function invitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class);
    }

    /** Equipment assigned to team. */
    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }

    /** Get all members (accepted). */
    public function activeMembers()
    {
        return $this->members()->where('left_at', null);
    }

    /** Check if team is private. */
    public function isPrivate(): bool
    {
        return $this->is_private;
    }

    /** Get team color for UI. */
    public function getColorAttribute(): string
    {
        return $this->color ?? '#3B82F6';
    }

    /** Scope for public teams. */
    public function scopePublic($query)
    {
        return $query->where('is_private', false);
    }

    /** Scope by organization. */
    public function scopeOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }
}
