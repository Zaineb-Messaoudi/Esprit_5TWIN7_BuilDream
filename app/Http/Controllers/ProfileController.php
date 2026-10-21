<?php

namespace App\Http\Controllers;

use App\DTOs\UserProfileData;
use App\Http\Requests\ProfileUpdateRequest;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $profileData = UserProfileData::fromRequest($request);
        $emailChanged = $user->email !== $profileData->email;

        $this->userService->updateProfile($user, $profileData);

        if ($emailChanged) {
            $user->email_verified_at = null;
            $user->save();
            $user->sendEmailVerificationNotification();
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if ($user->isAdmin()) {
            throw ValidationException::withMessages([
                'user' => __('Administrator accounts cannot be deleted from the profile page.'),
            ]);
        }

        $hasMarketplaceHistory = $user->reservations()->exists()
            || $user->rentals()->exists()
            || $user->equipment()->exists();

        if ($hasMarketplaceHistory) {
            DB::transaction(function () use ($user): void {
                \App\Models\ReservationAssistantRequest::query()->where('user_id', $user->id)->delete();
                $user->serviceProviderProfile()->delete();
                $user->notifications()->delete();

                if (Schema::hasTable('sessions')) {
                    DB::table('sessions')->where('user_id', $user->id)->delete();
                }

                $profilePhoto = $user->profile_photo_path;
                $user->forceFill([
                    'name' => 'Deleted account',
                    'email' => 'deleted+'.$user->id.'+'.Str::uuid().'@example.invalid',
                    'email_verified_at' => null,
                    'password' => Hash::make(Str::random(64)),
                    'remember_token' => null,
                    'phone_number' => null,
                    'address' => null,
                    'profile_photo_path' => null,
                    'google_id' => null,
                    'facebook_id' => null,
                    'two_factor_secret' => null,
                    'two_factor_confirmed_at' => null,
                    'two_factor_recovery_codes' => null,
                    'notification_preferences' => null,
                    'current_organization_id' => null,
                    'current_team_id' => null,
                    'organization_preferences' => null,
                ])->save();

                if ($profilePhoto) {
                    Storage::disk('public')->delete($profilePhoto);
                }
            });
        } else {
            Auth::logout();
            $user->delete();
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
