<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="h-full scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="pusher-key" content="{{ env('PUSHER_APP_KEY') }}">
    <meta name="pusher-cluster" content="{{ env('PUSHER_APP_CLUSTER', 'mt1') }}">
    @if(auth()->check())
        <meta name="user-id" content="{{ auth()->id() }}">
    @endif
    <meta name="description" content="@yield('description', __('Rent and share portable solar panels, batteries and small wind turbines between neighbours.'))">
    <meta name="theme-color" content="#ffffff">
    <link rel="icon" type="image/png" href="{{ asset('images/brand/solarshare-icon.png') }}">

    <title>{{ __($title ?? 'Home') }} | SolarShare</title>

    <script>
        (function () {
            const savedDir = localStorage.getItem('dir');
            if (savedDir) document.documentElement.setAttribute('dir', savedDir);
            if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
            if (localStorage.getItem('navigation-layout') === 'sidebar') document.documentElement.classList.add('nav-sidebar');
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
    @stack('head')
</head>

<body class="flex min-h-screen flex-col bg-white font-outfit text-gray-700 antialiased dark:bg-gray-900 dark:text-gray-300">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:start-4 focus:top-4 focus:z-999999 focus:rounded-lg focus:bg-white focus:px-4 focus:py-3 focus:text-sm focus:font-medium focus:text-gray-900 focus:shadow-theme-lg dark:focus:bg-gray-900 dark:focus:text-white">
        {{ __('Skip to main content') }}
    </a>

    @include('layouts.partials.front-navbar')

    <div class="front-content-shell flex flex-1 flex-col">
        @if (session('status'))
            <div class="border-b border-success-200 bg-success-50 px-6 py-3 text-center text-theme-sm text-success-700 dark:border-success-500/30 dark:bg-success-500/15 dark:text-success-400 sm:px-8 lg:px-10" role="status">
                {{ session('status') }}
            </div>
        @endif

        <main id="main-content" tabindex="-1" class="flex-1 outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-brand-500">
            @yield('content')
        </main>

        @include('layouts.partials.front-footer')
    </div>

    @stack('scripts')
</body>

</html>
