<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="h-full bg-gray-50 dark:bg-gray-900">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@hasSection('title')@yield('title')@else{{ $title ?? 'Page' }}@endif | SolarShare Admin</title>

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
                    this.updateTheme();
                },
                theme: 'light',
                set(value) {
                    this.theme = value === 'dark' ? 'dark' : 'light';
                    localStorage.setItem('theme', this.theme);
                    this.updateTheme();
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
    @if (View::hasSection('full-bleed'))
        @yield('content')
    @else
        <div class="flex min-h-screen items-center justify-center p-4">
            @yield('content')
        </div>
    @endif
</body>

@stack('scripts')
</html>
