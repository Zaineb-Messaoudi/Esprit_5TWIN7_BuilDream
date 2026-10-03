<x-guest-layout :title="__('Reset your password')">
    <div class="mb-8">
        <h1 class="text-title-md font-semibold text-gray-900 dark:text-white/90">{{ __('Reset your password') }}</h1>
        <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ __('Enter your email and we will send you a password reset link.') }}</p>
    </div>

    <x-auth-session-status class="mb-5" :status="session('status')" />

    <form id="forgot-password-form" method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="space-y-5">
            <x-auth.form-field name="email" :label="__('Email address')" type="email" autocomplete="username" inputmode="email" :required="true" :autofocus="true" />
            <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900">
                {{ __('Send password reset link') }}
            </button>
        </div>
    </form>

    <p class="mt-7 text-center text-sm text-gray-600 dark:text-gray-400">
        <a href="{{ route('login') }}" class="rounded-sm font-medium text-brand-600 hover:text-brand-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-brand-400 dark:hover:text-brand-300">{{ __('Back to sign in') }}</a>
    </p>
</x-guest-layout>
