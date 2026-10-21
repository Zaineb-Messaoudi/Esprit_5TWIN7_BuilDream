@extends('layouts.app')

@section('content')
<div class="space-y-6">
    {{-- Breadcrumb: Dashboard / Rental Analytics --}}
    <x-common.page-breadcrumb>
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="text-gray-500 transition-colors hover:text-brand-600 dark:text-gray-400">Dashboard</a>
            <span class="text-gray-400">/</span>
            <span class="text-gray-800 dark:text-white">Rental Analytics</span>
        </div>
    </x-common.page-breadcrumb>

    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">Rental Analytics</h1>
        <a href="{{ route('admin.rental-insights.overdue') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">View overdue rentals</a>
    </div>

    {{-- Main figures: one card per figure ($kpis comes from RentalInsightsService::kpis()) --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @php
            $cards = [
                ['Total rentals',        $kpis['total_rentals'],                              'text-gray-800 dark:text-white'],
                ['Active rentals',       $kpis['active_rentals'],                             'text-success-600'],
                ['Overdue rentals',      $kpis['overdue_rentals'],                            $kpis['overdue_rentals'] > 0 ? 'text-error-600' : 'text-gray-800 dark:text-white'],
                ['Revenue (TND)',        number_format($kpis['revenue'], 2),                  'text-gray-800 dark:text-white'],
                ['Deposits held (TND)',  number_format($kpis['deposits_held'], 2),            'text-gray-800 dark:text-white'],
                ['Contracts to sign',    $kpis['pending_signatures'],                         $kpis['pending_signatures'] > 0 ? 'text-warning-600' : 'text-gray-800 dark:text-white'],
            ];
        @endphp
        @foreach ($cards as [$label, $value, $colorClass])
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p>
                <p class="mt-2 text-title-sm font-semibold {{ $colorClass }}">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Rentals by status: one horizontal bar per status (a <progress> element, no JavaScript needed) --}}
        <x-common.component-card :title="__('Rentals by status')">
            <ul class="space-y-4">
                @foreach ($statuses as $row)
                    <li>
                        <div class="mb-1 flex justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-300">{{ $row['label'] }}</span>
                            <span class="font-medium text-gray-800 dark:text-white">{{ $row['count'] }}</span>
                        </div>
                        <progress class="h-2 w-full accent-brand-500" value="{{ $row['count'] }}" max="{{ $statusMax }}" aria-label="{{ $row['label'] }}"></progress>
                    </li>
                @endforeach
            </ul>
        </x-common.component-card>

        {{-- Rentals starting in each of the last 6 months --}}
        <x-common.component-card :title="__('Rentals per month')" :desc="__('Last 6 months, cancelled rentals excluded.')">
            <ul class="space-y-4">
                @foreach ($months as $row)
                    <li>
                        <div class="mb-1 flex justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-300">{{ $row['label'] }}</span>
                            <span class="text-gray-800 dark:text-white">
                                <span class="font-medium">{{ $row['count'] }}</span>
                                <span class="text-gray-500 dark:text-gray-400">· {{ number_format($row['revenue'], 2) }} TND</span>
                            </span>
                        </div>
                        <progress class="h-2 w-full accent-brand-500" value="{{ $row['count'] }}" max="{{ $monthMax }}" aria-label="{{ $row['label'] }}"></progress>
                    </li>
                @endforeach
            </ul>
        </x-common.component-card>

        {{-- Contracts --}}
        <x-common.component-card :title="__('Contracts')">
            <dl class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Draft</dt>
                    <dd class="mt-1 text-title-sm font-semibold text-gray-800 dark:text-white">{{ $kpis['contracts']['draft'] }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Signed</dt>
                    <dd class="mt-1 text-title-sm font-semibold text-success-600">{{ $kpis['contracts']['signed'] }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Terminated</dt>
                    <dd class="mt-1 text-title-sm font-semibold text-gray-800 dark:text-white">{{ $kpis['contracts']['terminated'] }}</dd>
                </div>
            </dl>
        </x-common.component-card>

        {{-- Extension requests --}}
        <x-common.component-card :title="__('Extension requests')">
            <dl class="grid grid-cols-2 gap-4 text-center sm:grid-cols-4">
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Pending</dt>
                    <dd class="mt-1 text-title-sm font-semibold text-warning-600">{{ $kpis['extensions']['pending'] }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Approved</dt>
                    <dd class="mt-1 text-title-sm font-semibold text-success-600">{{ $kpis['extensions']['approved'] }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Rejected</dt>
                    <dd class="mt-1 text-title-sm font-semibold text-error-600">{{ $kpis['extensions']['rejected'] }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Approval rate</dt>
                    {{-- null when no request was decided yet --}}
                    <dd class="mt-1 text-title-sm font-semibold text-gray-800 dark:text-white">{{ $kpis['extensions']['approval_rate'] !== null ? $kpis['extensions']['approval_rate'].'%' : '—' }}</dd>
                </div>
            </dl>
        </x-common.component-card>
    </div>

    {{-- Equipment rented most often --}}
    <x-common.component-card :title="__('Most rented equipment')">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[400px] border-collapse text-start text-sm">
                <thead>
                    <tr class="bg-gray-50 text-xs uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        <th class="p-3 border-b dark:border-gray-700">Equipment</th>
                        <th class="p-3 border-b dark:border-gray-700">Rentals</th>
                        <th class="p-3 border-b dark:border-gray-700">Revenue (TND)</th>
                    </tr>
                </thead>
                <tbody class="text-gray-800 dark:text-white">
                    @forelse ($topEquipment as $item)
                        <tr class="border-b dark:border-gray-700">
                            <td class="p-3 font-medium">{{ $item['name'] }}</td>
                            <td class="p-3">{{ $item['rentals'] }}</td>
                            <td class="p-3">{{ number_format($item['revenue'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="p-6 text-center text-gray-500 dark:text-gray-400">No rentals yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-common.component-card>
</div>
@endsection