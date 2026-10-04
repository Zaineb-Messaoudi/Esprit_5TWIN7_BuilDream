<x-guest-layout :title="__('Confirm your password')">
    <div class="mb-8">
        <h1 class="text-title-md font-semibold text-gray-900 dark:text-white/90">{{ __('Confirm your password') }}</h1>
        <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ __('This is a secure area. Please confirm your password before continuing.') }}</p>
    </div>

    <form id="confirm-password-form" method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="space-y-5">
            <x-auth.form-field name="password" :label="__('Password')" type="password" autocomplete="current-password" :required="true" :autofocus="true" />
            <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900">
                {{ __('Confirm') }}
            </button>
        </div>
    </form>
</x-guest-layout>
