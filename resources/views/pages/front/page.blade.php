@extends('layouts.front')

@section('content')
    @if (($page['shell'] ?? null) === 'account')
        <section class="relative isolate overflow-hidden bg-gray-950 text-white">
            <div class="pointer-events-none absolute inset-0 -z-10 bg-linear-to-br from-gray-950 via-brand-950 to-gray-900" aria-hidden="true"></div>
            <div class="hero-energy-grid pointer-events-none absolute inset-0 -z-10 opacity-20" aria-hidden="true"></div>
            <div class="mx-auto flex w-full max-w-7xl flex-wrap items-center justify-between gap-4 px-6 py-8 sm:px-8 sm:py-10 lg:px-10">
                <div>
                    <p class="text-theme-xs font-semibold uppercase tracking-[0.2em] text-brand-200">{{ __('Your SolarShare space') }}</p>
                    <h1 class="mt-2 text-title-sm font-semibold tracking-tight text-white sm:text-title-md">{{ __($page['title']) }}</h1>
                </div>
                <span class="inline-flex items-center gap-2 rounded-full border border-warning-300/25 bg-warning-300/10 px-3 py-2 text-theme-xs font-medium text-warning-200">
                    <span class="size-2 rounded-full bg-warning-500" aria-hidden="true"></span>
                    {{ __('UI preview — sample data') }}
                </span>
            </div>
        </section>
        <div class="mx-auto w-full max-w-7xl px-6 py-8 sm:px-8 sm:py-10 lg:px-10">
            <div class="mx-auto max-w-6xl space-y-8">
                <div class="flex items-start gap-3 rounded-2xl border border-warning-200 bg-warning-50 px-4 py-4 text-theme-sm leading-6 text-warning-800 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-300" role="status">
                    <span class="mt-0.5" aria-hidden="true">✳</span>
                    <p><span class="font-semibold">{{ __('Visual prototype.') }}</span> {{ __('Sample information is not connected to an account or saved.') }}</p>
                </div>
                @foreach ($page['sections'] as $section)
                    <x-front.section :s="$section" :account="true" />
                @endforeach
            </div>
        </div>
    @else
        @if (in_array($slug, ['reserve', 'payment', 'confirmed', 'invoice'], true))
            <div class="mx-auto w-full max-w-7xl px-6 pt-6 sm:px-8 lg:px-10">
                <div class="rounded-xl border border-warning-200 bg-warning-50 px-4 py-3 text-theme-sm text-warning-800 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-300" role="status">
                    {{ __('Booking flow preview only. No reservation is created and no payment is processed.') }}
                </div>
            </div>
        @endif
        @foreach ($page['sections'] as $section)
            <x-front.section :s="$section" />
        @endforeach
    @endif
@endsection
