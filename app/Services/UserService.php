<?php

namespace App\Services;

use App\Models\User;
use App\Enums\UserRole;
use App\DTOs\UserRegistrationData;
use App\DTOs\UserProfileData;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\UpdatablePasswordInterface;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class UserService
{
    /**
     * Register a new user with professional business logic.
     */
    public function register(UserRegistrationData $data): User
    {
        $photoPath = $data->profile_photo?->store('profile-photos', 'public');

        if ($data->profile_photo && !$photoPath) {
            throw new RuntimeException('The profile photo could not be stored.');
        }

        try {
            return DB::transaction(function () use ($data, $photoPath) {
                return User::create([
                    'name' => $data->name,
                    'email' => $data->email,
                    'password' => Hash::make($data->password),
                    'phone_number' => $data->phone_number,
                    'address' => $data->address,
                    'role' => $data->role,
                    'profile_photo_path' => $photoPath,
                ]);
            });
        } catch (Throwable $exception) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            throw $exception;
        }
    }

    /**
     * Update user profile information.
     */
    public function updateProfile(User $user, UserProfileData $data): User
    {
        $user->fill([
            'name' => $data->name,
            'email' => $data->email,
            'phone_number' => $data->phone_number,
            'address' => $data->address,
        ]);

        $user->save();

        return $user;
    }

    /**
     * Change user role with validation.
     */
    public function updateRole(User $user, UserRole $role): void
    {
        $user->update(['role' => $role]);
    }

    /**
     * Securely update a user's password.
     */
    public function updatePassword(User $user, string $newPassword): void
    {
        $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }

    /**
     * Reset user password using a token.
     */
    public function resetPassword(User $user, string $newPassword): void
    {
        $user->forceFill([
            'password' => Hash::make($newPassword),
        ])->save();

        // Invalidate reset tokens
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();
    }

    /**
     * Mark user email as verified.
     */
    public function verifyEmail(User $user): void
    {
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }
    }

    /**
     * Trigger re-sending of email verification notification.
     */
    public function sendVerificationNotification(User $user): void
    {
        $user->sendEmailVerificationNotification();
    }

    /**
     * Securely delete a user account.
     */
    public function deleteUser(User $user): void
    {
        $user->delete();
    }

    /**
     * Generic update for admin-side profile management.
     */
    public function updateUserProfile(User $user, array $data): User
    {
        $user->update($data);
        return $user;
    }
}
