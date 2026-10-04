@props(['title' => __('Authentication')])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="h-full bg-gray-50 dark:bg-gray-900">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#ffffff">

        <title>{{ __($title) }} | SolarShare Admin</title>

        <script>
            const savedDir = localStorage.getItem('dir');
            if (savedDir) {
                document.documentElement.setAttribute('dir', savedDir);
            }
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.classList.add('dark');
                document.documentElement.setAttribute('data-color-scheme', 'dark');
            }
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:start-4 focus:top-4 focus:z-999999 focus:rounded-lg focus:bg-white focus:px-4 focus:py-3 focus:text-sm focus:font-medium focus:text-brand-700 focus:shadow-theme-lg dark:focus:bg-gray-900 dark:focus:text-brand-300">{{ __('Skip to main content') }}</a>
        <div class="fixed end-4 top-4 z-999999"><x-locale-switcher /></div>
        <div class="flex min-h-screen items-center justify-center p-4">
            <main id="main-content" tabindex="-1" class="grid min-h-[calc(100vh-2rem)] w-full max-w-6xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xl outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:border-gray-800 dark:bg-gray-900 lg:grid-cols-2">
                <section class="flex items-center justify-center px-5 py-10 sm:px-10 lg:px-12">
                    <div class="w-full max-w-md">
                        <a href="{{ route('login') }}" class="mb-8 inline-flex items-center gap-3 rounded-sm text-lg font-semibold text-gray-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-white lg:hidden">
                            <span class="flex size-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-300" aria-hidden="true">
                                <svg class="size-6" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 2.75v2.5m0 13.5v2.5M2.75 12h2.5m13.5 0h2.5M5.46 5.46l1.77 1.77m9.54 9.54 1.77 1.77m0-13.08-1.77 1.77m-9.54 9.54-1.77 1.77M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                                </svg>
                            </span>
                            SolarShare
                        </a>
                        {{ $slot }}
                    </div>
                </section>

                <x-auth.split-panel />
            </main>
        </div>
    </body>
</html>
