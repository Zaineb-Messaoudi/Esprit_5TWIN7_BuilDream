@extends('layouts.front')
@section('content')
<section class="relative isolate overflow-hidden bg-gray-950 text-white">
    <div class="pointer-events-none absolute inset-0 -z-10 bg-linear-to-br from-gray-950 via-brand-950 to-gray-900" aria-hidden="true"></div>
    <div class="mx-auto flex max-w-7xl flex-wrap items-end justify-between gap-5 px-4 py-9 sm:px-8 sm:py-12 lg:px-10">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-[0.2em] text-warning-300">{{ __('Owner workspace') }} <span class="mx-1 text-gray-500">/</span> {{ __('Equipment') }}</p>
            <h1 class="mt-3 text-title-sm font-semibold sm:text-title-md">{{ $equipment->name }}</h1>
            <p class="mt-2 text-theme-sm text-gray-300">{{ $equipment->brand }} {{ $equipment->model }} · {{ $equipment->location }}</p>
        </div>
        <a href="{{ route('front.my-equipment-edit', $equipment) }}" class="button-base button-primary min-h-11 px-4 text-theme-sm">{{ __('Edit equipment') }}</a>
    </div>
</section>
@include('pages.front.partials.owner-nav')
<main class="mx-auto w-full max-w-7xl space-y-7 px-4 py-7 sm:px-8 sm:py-10 lg:px-10">
    <nav aria-label="{{ __('Breadcrumb') }}" class="flex items-center gap-2 text-theme-xs text-gray-500">
        <a href="{{ route('front.my-equipment') }}" class="hover:text-brand-700">{{ __('My equipment') }}</a>
        <span aria-hidden="true">/</span>
        <span aria-current="page">{{ $equipment->name }}</span>
    </nav>
    <div class="grid gap-6 lg:grid-cols-[1fr_0.8fr]">
        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-7">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Listing details') }}</h2>
            <dl class="mt-6 grid gap-5 sm:grid-cols-2">
                @foreach([__('Category') => $equipment->category->name, __('Location') => $equipment->location, __('Price per day') => number_format((float) $equipment->price_per_day, 2).' TND', __('Condition') => __(ucfirst($equipment->condition)), __('Status') => __(ucfirst($equipment->status)), __('Brand / model') => trim($equipment->brand.' '.$equipment->model)] as $label => $value)
                <div>
                    <dt class="text-theme-xs text-gray-500">{{ $label }}</dt>
                    <dd class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $value ?: '—' }}</dd>
                </div>
                @endforeach
            </dl>
            <div class="mt-6 border-t border-gray-100 pt-5 dark:border-gray-800">
                <h3 class="font-semibold text-gray-900 dark:text-white">{{ __('Description') }}</h3>
                <p class="mt-2 text-theme-sm leading-6 text-gray-600 dark:text-gray-300">{{ $equipment->description ?: __('No description provided.') }}</p>
            </div>
        </section>
        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-7">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Energy profile') }}</h2>
            @if($equipment->energyProfile)
            <dl class="mt-6 space-y-4">
                @foreach(['power_watts' => 'Power (W)', 'voltage' => 'Voltage (V)', 'capacity_wh' => 'Capacity (Wh)', 'efficiency' => 'Efficiency (%)', 'technology' => 'Technology', 'max_output' => 'Max output (W)', 'operating_duration' => 'Operating duration (h)'] as $key => $label)
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 pb-3 text-theme-sm dark:border-gray-800">
                    <dt class="text-gray-500">{{ __($label) }}</dt>
                    <dd class="font-semibold text-gray-900 dark:text-white">{{ $equipment->energyProfile->{$key} ?? '—' }}</dd>
                </div>
                @endforeach
            </dl>
            @else
            <p class="mt-5 text-theme-sm text-gray-500">{{ __('No energy profile has been added yet.') }}</p>
            @endif
        </section>
    </div>

    {{-- Maintenance History --}}
    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-7">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Maintenance history') }}</h2>
        @if($equipment->maintenances->isEmpty())
            <p class="mt-4 text-theme-sm text-gray-500">{{ __('No maintenance records found.') }}</p>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[800px] text-start text-theme-xs">
                    <thead class="bg-gray-50 text-gray-500 dark:bg-white/5 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3 text-start font-medium">{{ __('Reference') }}</th>
                            <th class="px-4 py-3 text-start font-medium">{{ __('Period') }}</th>
                            <th class="px-4 py-3 text-start font-medium">{{ __('Reason') }}</th>
                            <th class="px-4 py-3 text-start font-medium">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-end font-medium">{{ __('Cost') }}</th>
                            <th class="px-4 py-3 text-start font-medium">{{ __('Report') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($equipment->maintenances as $maintenance)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-3 font-medium">#{{ $maintenance->id }}</td>
                            <td class="px-4 py-3">
                                {{ $maintenance->start_date->format('d/m/Y') }}
                                @if($maintenance->end_date)
                                    → {{ $maintenance->end_date->format('d/m/Y') }}
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $maintenance->reason }}</td>
                            <td class="px-4 py-3">
                                <x-ui.badge :color="$maintenance->status === 'completed' ? 'success' : ($maintenance->status === 'in_progress' ? 'warning' : 'default')">
                                    {{ ucfirst($maintenance->status) }}
                                </x-ui.badge>
                            </td>
                            <td class="px-4 py-3 text-end font-medium">{{ number_format((float) $maintenance->cost, 2) }} TND</td>
                            <td class="px-4 py-3">
                                @if($maintenance->report)
                                    <a href="{{ route('technical.reports.show', $maintenance->report) }}" class="text-brand-600 hover:underline dark:text-brand-400">{{ __('View report') }}</a>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">{{ __('No report') }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        <tr class="bg-gray-50 dark:bg-gray-800/50 font-bold">
                            <td class="px-4 py-3" colspan="4">{{ __('Total') }}</td>
                            <td class="px-4 py-3 text-end">{{ number_format($equipment->maintenances->sum('cost'), 2) }} TND</td>
                            <td class="px-4 py-3"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</main>
@endsection