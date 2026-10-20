@extends('layouts.app')

@section('content')
<div class="space-y-6">
    {{-- Breadcrumb: Dashboard / Overdue Rentals --}}
    <x-common.page-breadcrumb>
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="text-gray-500 transition-colors hover:text-brand-600 dark:text-gray-400">Dashboard</a>
            <span class="text-gray-400">/</span>
            <span class="text-gray-800 dark:text-white">Overdue Rentals</span>
        </div>
    </x-common.page-breadcrumb>

    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">Overdue Rentals</h1>
        <a href="{{ route('admin.rental-insights.dashboard') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Back to analytics</a>
    </div>

    {{-- Summary: how many rentals are late and how much the late fees add up to --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm text-gray-500 dark:text-gray-400">Overdue rentals</p>
            <p class="mt-2 text-title-sm font-semibold {{ $rows->count() > 0 ? 'text-error-600' : 'text-gray-800 dark:text-white' }}">{{ $rows->count() }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm text-gray-500 dark:text-gray-400">Late fees to collect (TND)</p>
            <p class="mt-2 text-title-sm font-semibold text-gray-800 dark:text-white">{{ number_format($totalFee, 2) }}</p>
        </div>
    </div>

    <x-common.component-card :title="__('Late rentals')" :desc="__('Active rentals whose end date has passed.')">
        {{-- How the fee is calculated --}}
        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
            Late fee = daily rate × late days × {{ rtrim(rtrim(number_format($multiplier, 2), '0'), '.') }}.
            The contract states that late returns are charged by the day.
        </p>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] border-collapse text-start">
                <thead>
                    <tr class="bg-gray-50 text-xs uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        <th class="p-3 border-b dark:border-gray-700">Rental</th>
                        <th class="p-3 border-b dark:border-gray-700">Renter</th>
                        <th class="p-3 border-b dark:border-gray-700">Equipment</th>
                        <th class="p-3 border-b dark:border-gray-700">Due date</th>
                        <th class="p-3 border-b dark:border-gray-700">Days late</th>
                        <th class="p-3 border-b dark:border-gray-700">Daily rate</th>
                        <th class="p-3 border-b dark:border-gray-700">Late fee</th>
                        <th class="p-3 border-b dark:border-gray-700">Delay risk</th>
                    </tr>
                </thead>
                <tbody class="text-gray-800 dark:text-white">
                    {{-- Each $row comes from RentalInsightsController@overdue --}}
                    @forelse ($rows as $row)
                        <tr class="border-b transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800/50">
                            <td class="p-3">
                                <a href="{{ route('admin.rentals.show', $row['rental']) }}" class="font-medium text-brand-600 underline-offset-4 hover:underline dark:text-brand-400">{{ $row['rental']->reference }}</a>
                            </td>
                            <td class="p-3">{{ $row['rental']->user?->name ?? '—' }}</td>
                            <td class="p-3">{{ $row['rental']->equipment?->name ?? '—' }}</td>
                            <td class="p-3 text-sm">{{ $row['rental']->end_date->format('d/m/Y') }}</td>
                            <td class="p-3">
                                <x-ui.badge color="error">{{ $row['late_days'] }} {{ \Illuminate\Support\Str::plural('day', $row['late_days']) }}</x-ui.badge>
                            </td>
                            <td class="p-3">{{ number_format($row['daily_rate'], 2) }}</td>
                            <td class="p-3 font-semibold">{{ number_format($row['late_fee'], 2) }}</td>
                            {{-- Delay risk score (0-100) computed by LateRiskService --}}
                            <td class="p-3">
                                <x-ui.badge :color="$row['risk']['color']">{{ $row['risk']['label'] }} ({{ $row['risk']['score'] }})</x-ui.badge>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">No overdue rentals. Every active rental is within its dates.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-common.component-card>
</div>
@endsection