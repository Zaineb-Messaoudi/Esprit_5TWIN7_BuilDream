@extends('layouts.app')

@section('content')
    @php
        $values = [48, 68, 55, 82, 64, 91, 72, 86, 61, 77, 95, 73];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    @endphp

    <div class="space-y-6">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Charts / {{ $title }}</p>
            <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Solar equipment revenue by month · illustrative dashboard data</p>
        </div>

        <x-common.component-card :title="$title . ' overview'">
            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-brand-500"></span>Current year</span>
                <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-gray-300 dark:bg-gray-600"></span>Previous year</span>
            </div>
            @if (in_array($type, ['pie', 'donut'], true))
                <div class="mt-8 flex flex-col items-center gap-8 md:flex-row md:justify-center">
                    <div class="relative h-56 w-56">
                        <svg viewBox="0 0 120 120" class="h-full w-full -rotate-90" role="img" aria-label="{{ $title }} distribution">
                            <circle cx="60" cy="60" r="44" fill="none" stroke="currentColor" stroke-width="{{ $type === 'donut' ? 18 : 44 }}" class="text-brand-500" stroke-dasharray="138 276" />
                            <circle cx="60" cy="60" r="44" fill="none" stroke="currentColor" stroke-width="{{ $type === 'donut' ? 18 : 44 }}" class="text-blue-light-500" stroke-dasharray="83 276" stroke-dashoffset="-138" />
                            <circle cx="60" cy="60" r="44" fill="none" stroke="currentColor" stroke-width="{{ $type === 'donut' ? 18 : 44 }}" class="text-warning-500" stroke-dasharray="55 276" stroke-dashoffset="-221" />
                        </svg>
                        @if ($type === 'donut')
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-2xl font-semibold text-gray-800 dark:text-white">$84.2k</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Total revenue</span>
                            </div>
                        @endif
                    </div>
                    <div class="w-full max-w-xs space-y-4">
                        @foreach ([['Residential systems', '50%', 'bg-brand-500'], ['Inverters', '30%', 'bg-blue-light-500'], ['Storage', '20%', 'bg-warning-500']] as [$label, $share, $color])
                            <div class="flex items-center justify-between text-sm">
                                <span class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-300"><span class="h-2.5 w-2.5 rounded-full {{ $color }}"></span>{{ $label }}</span>
                                <span class="font-medium text-gray-800 dark:text-white">{{ $share }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif ($type === 'radial')
                <div class="mt-8 flex flex-col items-center gap-5">
                    <div class="relative h-48 w-48">
                        <svg viewBox="0 0 120 120" class="h-full w-full -rotate-90" role="img" aria-label="Quarterly sales goal: 78 percent">
                            <circle cx="60" cy="60" r="48" fill="none" stroke="currentColor" stroke-width="12" class="text-gray-100 dark:text-gray-800" />
                            <circle cx="60" cy="60" r="48" fill="none" stroke="currentColor" stroke-width="12" stroke-linecap="round" class="text-brand-500" stroke-dasharray="235 302" />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-3xl font-semibold text-gray-800 dark:text-white">78%</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">of quarterly goal</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="mt-8 h-64">
                    <div class="flex h-full items-end gap-2 border-b border-gray-100 pb-6 dark:border-gray-800 sm:gap-4">
                        @foreach ($values as $index => $value)
                            <div class="flex h-full flex-1 flex-col items-center justify-end gap-2">
                                @if (in_array($type, ['bar', 'area'], true))
                                    <div class="w-full rounded-t-md bg-brand-500/85 hover:bg-brand-600" style="height: {{ $value }}%" title="{{ $months[$index] }}: {{ $value }}%"></div>
                                @elseif ($type === 'line')
                                    <div class="flex w-full flex-col items-center justify-end" style="height: {{ $value }}%">
                                        <span class="h-3 w-3 rounded-full border-2 border-white bg-brand-500 shadow dark:border-gray-900"></span>
                                    </div>
                                @else
                                    <div class="flex w-full items-end justify-center gap-1" style="height: {{ max($value, 35) }}%">
                                        <span class="w-1/3 rounded-t bg-brand-500" style="height: {{ $value }}%"></span>
                                        <span class="w-1/3 rounded-t bg-blue-light-500" style="height: {{ max(100 - $value, 20) }}%"></span>
                                    </div>
                                @endif
                                <span class="text-[10px] text-gray-400 sm:text-xs">{{ $months[$index] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @if ($type === 'line' || $type === 'area')
                    <p class="mt-3 text-xs text-gray-400">Monthly trend is shown as a responsive point series.</p>
                @endif
            @endif
        </x-common.component-card>
    </div>
@endsection
