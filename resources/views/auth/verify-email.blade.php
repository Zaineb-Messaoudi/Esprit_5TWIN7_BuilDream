<x-guest-layout :title="__('Verify your email address')">
    <div class="mb-8">
        <h1 class="text-title-md font-semibold text-gray-900 dark:text-white/90">{{ __('Verify your email address') }}</h1>
        <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ __('Check your inbox for a verification link. If you did not receive it, request another below.') }}</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <p role="status" aria-live="polite" class="mb-5 rounded-lg border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-300">
            {{ __('A new verification link has been sent to your email address.') }}
        </p>
    @endif

    <div class="space-y-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900">
                {{ __('Resend verification email') }}
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="text-center">
            @csrf
            <button type="submit" class="rounded-sm px-2 py-1 text-sm font-medium text-gray-500 underline-offset-4 hover:text-gray-800 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-gray-400 dark:hover:text-white">
                {{ __('Log out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
