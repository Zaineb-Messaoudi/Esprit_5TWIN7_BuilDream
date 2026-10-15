<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="h-full bg-gray-50 dark:bg-gray-900">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">
    <link rel="icon" type="image/png" href="{{ asset('images/brand/solarshare-icon.png') }}">

    <title>@hasSection('title'){{ __($__env->yieldContent('title')) }}@else{{ __($title ?? 'Page') }}@endif | SolarShare Admin</title>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                init() {
                    const savedTheme = localStorage.getItem('theme');
                    this.theme = savedTheme === 'dark' ? 'dark' : 'light';
                    this.resolvedTheme = this.theme;
                    this.updateTheme();
                },
                theme: 'light',
                resolvedTheme: 'light',
                set(value) {
                    this.theme = value === 'dark' ? 'dark' : 'light';
                    this.resolvedTheme = this.theme;
                    localStorage.setItem('theme', this.theme);
                    this.updateTheme();
                },
                toggle() {
                    this.set(this.resolvedTheme === 'dark' ? 'light' : 'dark');
                },
                updateTheme() {
                    const html = document.documentElement;
                    if (this.theme === 'dark') {
                        html.classList.add('dark');
                        html.setAttribute('data-color-scheme', 'dark');
                    } else {
                        html.classList.remove('dark');
                        html.setAttribute('data-color-scheme', 'light');
                    }
                }
            });
        });
    </script>

    <script>
        (function() {
            const savedDir = localStorage.getItem('dir');
            if (savedDir) {
                document.documentElement.setAttribute('dir', savedDir);
            }
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark');
                document.documentElement.setAttribute('data-color-scheme', 'dark');
            }
        })();
    </script>
</head>

<body class="antialiased">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:start-4 focus:top-4 focus:z-999999 focus:rounded-lg focus:bg-white focus:px-4 focus:py-3 focus:text-sm focus:font-medium focus:text-brand-700 focus:shadow-theme-lg dark:focus:bg-gray-900 dark:focus:text-brand-300">
        {{ __('Skip to main content') }}
    </a>
    <div class="fixed end-4 top-4 z-999999"><x-locale-switcher /></div>
    @if (View::hasSection('full-bleed'))
        <div id="main-content" tabindex="-1" class="outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-brand-500">
            @yield('content')
        </div>
    @else
        <div class="flex min-h-screen items-center justify-center p-4">
            <div id="main-content" tabindex="-1" class="outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-brand-500">
                @yield('content')
            </div>
        </div>
    @endif
</body>

@stack('scripts')
</html>
