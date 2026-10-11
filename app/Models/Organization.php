<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Organization (tenant) model for multi-tenancy.
 */
class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'organizations';

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'domain',
        'description',
        'plan',
        'status',
        'settings',
        'billing_info',
        'trial_ends_at',
        'subscription_ends_at',
        'owner_id',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'billing_info' => 'array',
            'trial_ends_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
        ];
    }

    /** The organization owner. */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** Organization members. */
    public function members(): HasMany
    {
        return $this->hasMany(OrganizationMember::class);
    }

    /** Teams in this organization. */
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    /** Invitations to join organization. */
    public function invitations(): HasMany
    {
        return $this->hasMany(OrganizationInvitation::class);
    }

    /** Equipment owned by organization. */
    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }

    /** Rentals in this organization. */
    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    /** Reservations in this organization. */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /** Payments in this organization. */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** Check if organization is active. */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /** Check if organization is on trial. */
    public function isOnTrial(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at && $this->trial_ends_at > now();
    }

    /** Check if subscription is active. */
    public function hasActiveSubscription(): bool
    {
        return $this->subscription_ends_at && $this->subscription_ends_at > now();
    }

    /** Get plan limits. */
    public function getPlanLimits(): array
    {
        return match ($this->plan) {
            'free' => [
                'max_members' => 5,
                'max_teams' => 1,
                'max_equipment' => 5,
                'max_storage_mb' => 100,
                'api_access' => false,
                'custom_domain' => false,
                'sso' => false,
            ],
            'starter' => [
                'max_members' => 20,
                'max_teams' => 3,
                'max_equipment' => 25,
                'max_storage_mb' => 1000,
                'api_access' => true,
                'custom_domain' => false,
                'sso' => false,
            ],
            'professional' => [
                'max_members' => 100,
                'max_teams' => 10,
                'max_equipment' => 100,
                'max_storage_mb' => 10000,
                'api_access' => true,
                'custom_domain' => true,
                'sso' => true,
            ],
            'enterprise' => [
                'max_members' => null, // unlimited
                'max_teams' => null,
                'max_equipment' => null,
                'max_storage_mb' => null,
                'api_access' => true,
                'custom_domain' => true,
                'sso' => true,
            ],
            default => [],
        };
    }

    /** Check if organization has reached member limit. */
    public function hasMemberCapacity(): bool
    {
        $limits = $this->getPlanLimits();
        if (! $limits['max_members']) {
            return true;
        }

        return $this->members()->where('accepted_at', '!=', null)->count() < $limits['max_members'];
    }

    /** Scope for active organizations. */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /** Scope by plan. */
    public function scopePlan($query, string $plan)
    {
        return $query->where('plan', $plan);
    }
}
