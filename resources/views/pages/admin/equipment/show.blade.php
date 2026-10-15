@extends('layouts.app')
@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb>
        <a href="{{ route('admin.equipment.index') }}" class="text-gray-500 hover:text-brand-600">{{ __('Equipment') }}</a> / {{ $equipment->name }}
    </x-common.page-breadcrumb>
    <div class="flex items-center justify-between">
        <h1 class="text-title-md font-semibold text-gray-800 dark:text-white">{{ $equipment->name }}</h1>
        <a href="{{ route('admin.equipment.edit', $equipment) }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white">{{ __('Edit') }}</a>
    </div>
    <x-common.component-card>
        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <p class="text-xs text-gray-500">{{ __('Category') }}</p>
                <p class="font-medium">{{ $equipment->category->name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">{{ __('Owner') }}</p>
                <p class="font-medium">{{ $equipment->owner->name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">{{ __('Price per day') }}</p>
                <p class="font-medium">{{ number_format((float) $equipment->price_per_day, 2) }} TND</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">{{ __('Status') }}</p>
                <p class="font-medium">{{ __(ucfirst($equipment->status)) }}</p>
            </div>
            <div class="sm:col-span-2">
                <p class="text-xs text-gray-500">{{ __('Description') }}</p>
                <p>{{ $equipment->description ?: __('No description.') }}</p>
            </div>
        </div>
        @if($equipment->energyProfile)
        <div class="mt-8 border-t border-gray-200 pt-6 dark:border-gray-800">
            <h2 class="mb-4 font-semibold">{{ __('Energy profile') }}</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach(['power_watts' => 'Power (W)','voltage' => 'Voltage (V)','capacity_wh' => 'Capacity (Wh)','efficiency' => 'Efficiency (%)','technology' => 'Technology','max_output' => 'Max output (W)','operating_duration' => 'Operating duration (h)'] as $key => $label)
                <div>
                    <p class="text-xs text-gray-500">{{ __($label) }}</p>
                    <p>{{ $equipment->energyProfile->{$key} ?? '—' }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </x-common.component-card>

    {{-- Maintenance History --}}
    <x-common.component-card :title="__('Maintenance history')">
        @if($equipment->maintenances->isEmpty())
            <p class="text-gray-500 dark:text-gray-400">{{ __('No maintenance records found.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-800">
                            <th class="pb-2 font-medium">Reference</th>
                            <th class="pb-2 font-medium">Period</th>
                            <th class="pb-2 font-medium">Reason</th>
                            <th class="pb-2 font-medium">Status</th>
                            <th class="pb-2 font-medium text-right">Cost</th>
                            <th class="pb-2 font-medium">Report</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($equipment->maintenances as $maintenance)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="py-3 font-medium">#{{ $maintenance->id }}</td>
                            <td class="py-3">
                                {{ $maintenance->start_date->format('d/m/Y') }}
                                @if($maintenance->end_date)
                                    → {{ $maintenance->end_date->format('d/m/Y') }}
                                @endif
                            </td>
                            <td class="py-3">{{ $maintenance->reason }}</td>
                            <td class="py-3">
                                <x-ui.badge :color="$maintenance->status === 'completed' ? 'success' : ($maintenance->status === 'in_progress' ? 'warning' : 'default')">
                                    {{ ucfirst($maintenance->status) }}
                                </x-ui.badge>
                            </td>
                            <td class="py-3 text-right font-medium">{{ number_format((float) $maintenance->cost, 2) }} TND</td>
                            <td class="py-3">
                                @if($maintenance->report)
                                    <a href="{{ route('admin.technical.reports.show', $maintenance->report) }}" class="text-brand-600 hover:underline dark:text-brand-400">{{ __('View report') }}</a>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">{{ __('No report') }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        <tr class="bg-gray-50 dark:bg-gray-800/50 font-bold">
                            <td class="py-3" colspan="4">{{ __('Total') }}</td>
                            <td class="py-3 text-right">{{ number_format($equipment->maintenances->sum('cost'), 2) }} TND</td>
                            <td class="py-3"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </x-common.component-card>
</div>
@endsection
