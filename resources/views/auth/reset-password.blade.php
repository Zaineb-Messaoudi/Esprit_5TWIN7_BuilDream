<x-guest-layout :title="__('Choose a new password')">
    <div class="mb-8">
        <h1 class="text-title-md font-semibold text-gray-900 dark:text-white/90">{{ __('Choose a new password') }}</h1>
        <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ __('Set a new password to secure your account.') }}</p>
    </div>

    <form id="reset-password-form" method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="space-y-5">
            <x-auth.form-field name="email" :label="__('Email address')" type="email" :value="$request->email" autocomplete="username" inputmode="email" :required="true" :autofocus="true" />
            <x-auth.form-field name="password" :label="__('Password')" type="password" autocomplete="new-password" :required="true" />
            <x-auth.form-field name="password_confirmation" :label="__('Confirm password')" type="password" autocomplete="new-password" :required="true" />

            <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900">
                {{ __('Reset Password') }}
            </button>
        </div>
    </form>
</x-guest-layout>
