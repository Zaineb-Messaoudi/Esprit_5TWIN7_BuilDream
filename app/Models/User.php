<?php

namespace App\Models;

use App\Models\Notification as NotificationModel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Notification;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    /**
     * Send a notification using both the custom system and Laravel's facade.
     */
    public function notify($notification): void
    {
        // Send through custom system (database + email) for our custom notifications
        if ($notification instanceof \App\Notifications\BaseNotification) {
            $notification->recipientId = $this->id;
            $notification->save($this);

            return;
        }

        // For Laravel's built-in notifications (password reset, etc.), use the facade
        Notification::send($this, $notification);
    }

    /**
     * Get the user's notifications.
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(NotificationModel::class, 'notifiable');
    }

    /**
     * Get the user's unread notifications.
     */
    public function unreadNotifications(): MorphMany
    {
        return $this->notifications()->whereNull('read_at');
    }

    /**
     * Route notifications for the mail channel.
     */
    public function routeNotificationFor(string $driver): ?string
    {
        return $driver === 'mail' ? $this->email : null;
    }

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
        'aggregate_rating',
        'reviews_count',
        'detailed_ratings',
        'two_factor_secret',
        'two_factor_confirmed_at',
        'two_factor_recovery_codes',
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
            'aggregate_rating' => 'decimal:1',
            'reviews_count' => 'integer',
            'detailed_ratings' => 'array',
            'two_factor_secret' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_recovery_codes' => 'array',
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

    /**
     * Per-notification-type opt-in flags.
     *
     * Stored as a JSON column on the `users` table. Every notification class
     * exposes an `emailPreferenceKey()` (e.g. "payment_received") and the
     * base class consults this method before adding the "mail" channel.
     *
     * Defaults to true for every known key, so existing users keep receiving
     * emails until they explicitly opt out.
     */
    public function wantsEmail(string $key): bool
    {
        $preferences = $this->notification_preferences ?? [];

        if (! array_key_exists($key, $preferences)) {
            return true;
        }

        return (bool) $preferences[$key];
    }

    /**
     * Update the opt-in flags from the preferences form.
     */
    public function setNotificationPreferences(array $preferences): static
    {
        $this->notification_preferences = $preferences;

        return $this;
    }

    /**
     * Get all reviews written by this user (as reviewer).
     */
    public function writtenReviews(): HasMany
    {
        return $this->hasMany(\App\Models\Review::class, 'reviewer_id');
    }

    /**
     * Get all reviews received by this user (as reviewee).
     */
    public function receivedReviews(): HasMany
    {
        return $this->hasMany(\App\Models\Review::class, 'reviewee_id');
    }

    /**
     * Get public reviews received by this user.
     */
    public function publicReceivedReviews(): HasMany
    {
        return $this->receivedReviews()->where('is_public', true)->whereNotNull('published_at');
    }

    /**
     * Get disputes initiated by this user.
     */
    public function initiatedDisputes(): HasMany
    {
        return $this->hasMany(\App\Models\ReviewDispute::class, 'initiator_id');
    }

    /**
     * Get review responses written by this user.
     */
    public function reviewResponses(): HasMany
    {
        return $this->hasMany(\App\Models\ReviewResponse::class);
    }
}
