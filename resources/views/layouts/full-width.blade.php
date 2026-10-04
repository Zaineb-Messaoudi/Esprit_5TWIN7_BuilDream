<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="h-full bg-gray-50 dark:bg-gray-900">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? __('Full-width Layout') }} | SolarShare Admin</title>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                theme: 'light',
                resolvedTheme: 'light',
                init() {
                    this.set(localStorage.getItem('theme') === 'dark' ? 'dark' : 'light', false);
                },
                set(value, dispatch = true) {
                    this.theme = value === 'dark' ? 'dark' : 'light';
                    this.resolvedTheme = this.theme;
                    localStorage.setItem('theme', this.theme);
                    document.documentElement.classList.toggle('dark', this.theme === 'dark');
                    document.documentElement.dataset['colorScheme'] = this.theme;
                    if (dispatch) {
                        window.dispatchEvent(new CustomEvent('theme-changed', { detail: this.theme }));
                    }
                },
                toggle() {
                    this.set(this.resolvedTheme === 'dark' ? 'light' : 'dark');
                }
            });
        });
    </script>

    <script>
        document.documentElement.classList.toggle('dark', localStorage.getItem('theme') === 'dark');
    </script>
</head>

<body class="antialiased">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:start-4 focus:top-4 focus:z-999999 focus:rounded-lg focus:bg-white focus:px-4 focus:py-3 focus:text-sm focus:font-medium focus:text-brand-700 focus:shadow-theme-lg dark:focus:bg-gray-900 dark:focus:text-brand-300">
        {{ __('Skip to main content') }}
    </a>
    <header class="border-b border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="mx-auto flex max-w-(--breakpoint-2xl) items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('dashboard') }}" class="text-base font-semibold text-gray-800 dark:text-white/90">SolarShare Admin</a>
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-600 hover:text-brand-600 dark:text-gray-300 dark:hover:text-brand-300">{{ __('Dashboard') }}</a>
                <a href="{{ route('profile.overview') }}" class="text-sm font-medium text-gray-600 hover:text-brand-600 dark:text-gray-300 dark:hover:text-brand-300">{{ __('Profile') }}</a>
                <button type="button" @click="$store.theme.toggle()" :aria-label="$store.theme.resolvedTheme === 'dark' ? @js(__('Switch to light theme')) : @js(__('Switch to dark theme'))" class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-800">
                    <span aria-hidden="true" x-text="$store.theme.resolvedTheme === 'dark' ? '☀' : '☾'"></span>
                </button>
            </div>
        </div>
    </header>
    <main id="main-content" tabindex="-1" class="mx-auto w-full max-w-none p-4 outline-none sm:p-6 lg:p-8">
        @yield('content')
    </main>
</body>

@stack('scripts')
</html>
