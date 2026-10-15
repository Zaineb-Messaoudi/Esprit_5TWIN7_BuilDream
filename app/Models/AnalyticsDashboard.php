<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Analytics dashboard.
 */
class AnalyticsDashboard extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'analytics_dashboards';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'visibility',
        'layout',
        'widgets',
        'owner_id',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'layout' => 'array',
            'widgets' => 'array',
            'is_default' => 'boolean',
        ];
    }

    /** The dashboard owner. */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** Widgets in this dashboard. */
    public function widgets(): HasMany
    {
        return $this->hasMany(AnalyticsWidget::class)->orderBy('sort_order');
    }

    /** Check if user can view dashboard. */
    public function isVisibleTo(User $user): bool
    {
        if ($this->owner_id === $user->id) {
            return true;
        }

        return match ($this->visibility) {
            'public' => true,
            'team' => $this->owner->team_id === $user->team_id ?? false,
            'organization' => true, // All users in org
            default => false,
        };
    }

    /** Scope for user's dashboards. */
    public function scopeForUser($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('owner_id', $user->id)
                ->orWhere('visibility', 'public')
                ->orWhere(function ($qq) use ($user) {
                    $qq->where('visibility', 'team')
                        ->whereHas('owner', fn ($q) => $q->where('team_id', $user->team_id));
                });
        });
    }
}
