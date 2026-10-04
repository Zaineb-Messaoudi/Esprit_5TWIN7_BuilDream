@extends('layouts.fullscreen-layout')

@php($title = $title ?? __('Page not found'))

@section('content')
    <main class="relative flex min-h-[calc(100vh-2rem)] w-full flex-col items-center justify-center overflow-hidden p-6 text-center" aria-labelledby="not-found-title">
        <x-common.common-grid-shape />
        <div class="relative z-1 mx-auto w-full max-w-xl">
            <img src="{{ asset('images/error/404.svg') }}" alt="" class="mx-auto w-full max-w-[360px] dark:hidden" />
            <img src="{{ asset('images/error/404-dark.svg') }}" alt="" class="mx-auto hidden w-full max-w-[360px] dark:block" />
            <p class="mt-8 text-sm font-semibold uppercase tracking-wide text-brand-600 dark:text-brand-300">{{ __('Error 404') }}</p>
            <h1 id="not-found-title" class="mt-2 text-title-md font-semibold text-gray-800 dark:text-white/90 sm:text-title-lg">{{ __('Page not found') }}</h1>
            <p class="mx-auto mt-3 max-w-md text-sm leading-6 text-gray-600 dark:text-gray-400 sm:text-base">{{ __("We can't seem to find the page you are looking for.") }}</p>
            <a href="{{ route('dashboard') }}" class="mt-7 inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
                {{ __('Back to dashboard') }}
            </a>
        </div>
        <footer class="relative z-1 mt-12 text-xs text-gray-500 dark:text-gray-400">
            &copy; {{ now()->year }} {{ __('SolarShare Admin') }}
        </footer>
    </main>
@endsection
