@extends(auth()->user()->isAdmin() ? 'layouts.app' : 'layouts.front')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Account') }} / {{ __('Profile') }}</p>
            <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ __('Profile settings') }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Update your account details, change your password, or delete your account.') }}</p>
        </div>

        <x-common.component-card :title="__('Profile information')" :desc="__('Keep your name and contact details up to date.')">
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
                @csrf
                @method('PATCH')
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="profile-name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Name') }}</label>
                        <input id="profile-name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name" @class([
                            'h-11 w-full rounded-lg border bg-white px-4 text-sm text-gray-800 focus:outline-none focus:ring-3 dark:bg-gray-900 dark:text-white',
                            'border-error-500 focus:ring-error-500/10' => $errors->has('name'),
                            'border-gray-300 focus:border-brand-400 focus:ring-brand-500/10 dark:border-gray-700' => !$errors->has('name'),
                        ]) />
                        @error('name')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="profile-email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Email address') }}</label>
                        <input id="profile-email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="email" @class([
                            'h-11 w-full rounded-lg border bg-white px-4 text-sm text-gray-800 focus:outline-none focus:ring-3 dark:bg-gray-900 dark:text-white',
                            'border-error-500 focus:ring-error-500/10' => $errors->has('email'),
                            'border-gray-300 focus:border-brand-400 focus:ring-brand-500/10 dark:border-gray-700' => !$errors->has('email'),
                        ]) />
                        @error('email')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="profile-phone" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Phone number') }}</label>
                        <input id="profile-phone" name="phone_number" type="tel" value="{{ old('phone_number', $user->phone_number) }}" autocomplete="tel" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 focus:border-brand-400 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        @error('phone_number')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="profile-address" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Address') }}</label>
                        <input id="profile-address" name="address" type="text" value="{{ old('address', $user->address) }}" autocomplete="street-address" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 focus:border-brand-400 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        @error('address')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-warning-200 bg-warning-50 p-4 dark:border-warning-500/20 dark:bg-warning-500/10">
                        <div>
                            <p class="text-sm font-medium text-warning-800 dark:text-warning-300">{{ __('Your email address is not verified.') }}</p>
                            @if (session('status') === 'verification-link-sent')
                                <p role="status" class="mt-1 text-xs text-success-700 dark:text-success-300">{{ __('A new verification link has been sent.') }}</p>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit" class="rounded-lg border border-warning-300 px-3 py-2 text-sm font-medium text-warning-800 hover:bg-warning-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-warning-500 dark:border-warning-500/30 dark:text-warning-300">{{ __('Resend verification email') }}</button>
                        </form>
                    </div>
                @endif

                <div class="flex flex-wrap items-center gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">{{ __('Save profile') }}</button>
                    @if (session('status') === 'profile-updated')
                        <p role="status" class="text-sm text-success-600 dark:text-success-400">{{ __('Profile updated successfully.') }}</p>
                    @endif
                </div>
            </form>
        </x-common.component-card>

        <x-common.component-card id="update-password" :title="__('Update password')" :desc="__('Use a unique password and confirm your current password before changing it.')">
            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div>
                        <label for="current_password" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Current password') }}</label>
                        <input id="current_password" name="current_password" type="password" autocomplete="current-password" required class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 focus:border-brand-400 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        @foreach ($errors->updatePassword->get('current_password') as $message)<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@endforeach
                    </div>
                    <div>
                        <label for="new_password" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('New password') }}</label>
                        <input id="new_password" name="password" type="password" autocomplete="new-password" required class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 focus:border-brand-400 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        @foreach ($errors->updatePassword->get('password') as $message)<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@endforeach
                    </div>
                    <div>
                        <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Confirm new password') }}</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 focus:border-brand-400 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">{{ __('Update password') }}</button>
                    @if (session('status') === 'password-updated')
                        <p role="status" class="text-sm text-success-600 dark:text-success-400">{{ __('Password updated successfully.') }}</p>
                    @endif
                </div>
            </form>
        </x-common.component-card>

        <x-common.component-card :title="__('Delete account')" :desc="__('Deleting your account permanently removes your account and cannot be undone.')">
            <form method="POST" action="{{ route('profile.destroy') }}" class="space-y-4">
                @csrf
                @method('DELETE')
                <div class="max-w-md">
                    <label for="delete-account-password" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Confirm with your current password') }}</label>
                    <input id="delete-account-password" name="password" type="password" autocomplete="current-password" required class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 focus:border-error-400 focus:outline-none focus:ring-3 focus:ring-error-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    @foreach ($errors->userDeletion->get('password') as $message)<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@endforeach
                </div>
                <button type="submit" class="rounded-lg border border-error-300 px-4 py-2.5 text-sm font-medium text-error-700 hover:bg-error-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-error-500 dark:border-error-500/30 dark:text-error-300 dark:hover:bg-error-500/10">{{ __('Delete my account') }}</button>
            </form>
        </x-common.component-card>
    </div>
@endsection
