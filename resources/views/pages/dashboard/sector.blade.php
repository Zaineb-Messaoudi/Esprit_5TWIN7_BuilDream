@extends('layouts.app')

@section('content')
    @php
        $chartPoints = collect($series)
            ->map(fn ($value, $index) => ($index * 560 / (count($series) - 1)) . ',' . (150 - $value * 1.5))
            ->implode(' ');
        $periods = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    @endphp

    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="mb-1 text-sm text-gray-500 dark:text-gray-400">Dashboard / {{ $title }}</p>
                <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
            </div>
            <label class="sr-only" for="dashboard-period">Reporting period</label>
            <select id="dashboard-period" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option>Last 12 months</option>
                <option>Last 30 days</option>
                <option>This year</option>
            </select>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:gap-6">
            @foreach ($metrics as $metric)
                <x-common.component-card :title="$metric['label']">
                    <div class="mt-3 flex flex-wrap items-end justify-between gap-2">
                        <p class="text-title-md font-semibold text-gray-800 dark:text-white/90">{{ $metric['value'] }}</p>
                        <x-ui.badge variant="light" :color="$metric['tone']">{{ $metric['change'] }}</x-ui.badge>
                    </div>
                </x-common.component-card>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-5">
            <x-common.component-card :title="$series_label" desc="Monthly performance for the current year" class="xl:col-span-3">
                <div class="flex justify-end">
                    <x-ui.badge variant="light" color="success">12.8% vs last year</x-ui.badge>
                </div>
                <div class="mt-6 overflow-hidden">
                    <svg role="img" aria-label="{{ $series_label }} trend chart" viewBox="0 0 600 190" class="h-56 w-full">
                        <title>{{ $series_label }} trend</title>
                        @foreach ([35, 75, 115, 155] as $y)
                            <line x1="0" y1="{{ $y }}" x2="600" y2="{{ $y }}" stroke="currentColor" class="text-gray-100 dark:text-gray-800" stroke-dasharray="4 5" />
                        @endforeach
                        <polyline points="{{ $chartPoints }}" fill="none" stroke="currentColor" class="text-brand-500" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                        @foreach ($series as $index => $value)
                            <circle cx="{{ $index * 560 / (count($series) - 1) }}" cy="{{ 150 - $value * 1.5 }}" r="4" fill="currentColor" class="text-brand-500" />
                        @endforeach
                    </svg>
                    <div class="mt-1 grid grid-cols-6 gap-2 text-center text-xs text-gray-400 sm:grid-cols-12">
                        @foreach ($periods as $period)
                            <span>{{ $period }}</span>
                        @endforeach
                    </div>
                </div>
            </x-common.component-card>

            <x-common.component-card title="Recent activity" desc="Latest updates across your account" class="xl:col-span-2">
                <div class="flex justify-end">
                    <button type="button" aria-label="More activity options" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 focus:outline-hidden focus:ring-2 focus:ring-brand-500 dark:hover:bg-gray-800">
                        <span aria-hidden="true">•••</span>
                    </button>
                </div>
                <div class="space-y-5">
                    @foreach ($rows as $row)
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-gray-800 dark:text-white/90">{{ $row['name'] }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $row['symbol'] }}</p>
                            </div>
                            <div class="shrink-0 text-end">
                                <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $row['value'] }}</p>
                                <p class="mt-1 text-xs {{ $row['tone'] === 'error' ? 'text-error-500' : ($row['tone'] === 'success' ? 'text-success-600' : 'text-brand-500') }}">{{ $row['change'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-common.component-card>
        </div>
    </div>
@endsection
