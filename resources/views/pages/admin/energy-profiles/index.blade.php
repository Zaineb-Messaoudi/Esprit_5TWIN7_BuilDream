@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb><a href="{{ route('admin.equipment.show', $equipment) }}" class="text-gray-500 hover:text-brand-600">{{ $equipment->name }}</a> / {{ __('Energy profile') }}</x-common.page-breadcrumb>
    <div class="flex flex-wrap items-center justify-between gap-4"><h1 class="text-title-md font-semibold text-gray-800 dark:text-white">{{ __('Energy profile') }}</h1>@if($profile)<a href="{{ route('admin.equipment.energy-profile.edit', $equipment) }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white">{{ __('Edit') }}</a>@else<a href="{{ route('admin.equipment.energy-profile.create', $equipment) }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white">{{ __('Create energy profile') }}</a>@endif</div>
    @if(session('status'))<p role="status" class="rounded-lg bg-success-50 px-4 py-3 text-sm text-success-700 dark:bg-success-500/10 dark:text-success-300">{{ __('Energy profile saved successfully.') }}</p>@endif
    <x-common.component-card :title="$equipment->name">
        @if($profile)
            <div class="grid gap-4 sm:grid-cols-3">@foreach(['power_watts' => 'Power (W)', 'voltage' => 'Voltage (V)', 'capacity_wh' => 'Capacity (Wh)', 'efficiency' => 'Efficiency (%)', 'technology' => 'Technology', 'max_output' => 'Max output (W)', 'operating_duration' => 'Operating duration (h)'] as $key => $label)<div><p class="text-xs text-gray-500">{{ __($label) }}</p><p class="font-medium text-gray-900 dark:text-white">{{ $profile->{$key} ?? '—' }}</p></div>@endforeach</div>
            <form class="mt-6" method="POST" action="{{ route('admin.equipment.energy-profile.destroy', $equipment) }}">@csrf @method('DELETE')<button type="submit" onclick="return confirm(@js(__('Delete this energy profile?')))" class="text-sm text-error-600 hover:underline">{{ __('Delete energy profile') }}</button></form>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No energy profile has been added yet.') }}</p>
        @endif
    </x-common.component-card>
</div>
@endsection
