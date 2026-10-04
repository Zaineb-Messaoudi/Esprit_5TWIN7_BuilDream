<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'address',
        'role',
        'role_setup_completed',
        'profile_photo_path',
        'google_id',
        'facebook_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => \App\Enums\UserRole::class,
            'role_setup_completed' => 'boolean',
        ];
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        return $this->profile_photo_path
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->profile_photo_path)
            : asset('images/user/owner.png');
    }

    public function isAdmin(): bool
    {
        return $this->role === \App\Enums\UserRole::ADMIN;
    }

    public function isUser(): bool
    {
        return $this->isBuyer();
    }

    public function isOwner(): bool
    {
        return $this->role === \App\Enums\UserRole::OWNER;
    }

    public function isBuyer(): bool
    {
        return in_array($this->role, [\App\Enums\UserRole::BUYER, \App\Enums\UserRole::USER], true);
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(\App\Models\Equipment::class, 'owner_id');
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(\App\Models\Rental::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(\App\Models\Reservation::class);
    }
}
