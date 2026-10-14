@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb>
        <a href="{{ route('admin.categories.index') }}" class="text-gray-500 hover:text-brand-600">{{ __('Categories') }}</a> / {{ $category->name }}
    </x-common.page-breadcrumb>

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-title-md font-semibold text-gray-800 dark:text-white">{{ $category->name }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $category->description ?: __('No description.') }}</p>
        </div>
        <a href="{{ route('admin.categories.edit', $category) }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white">{{ __('Edit') }}</a>
    </div>

    <x-common.component-card :title="__('Category details')">
        <div class="grid gap-4 sm:grid-cols-3">
            <div><p class="text-xs text-gray-500">{{ __('Status') }}</p><p class="font-medium text-gray-900 dark:text-white">{{ __(ucfirst($category->status)) }}</p></div>
            <div><p class="text-xs text-gray-500">{{ __('Equipment') }}</p><p class="font-medium text-gray-900 dark:text-white">{{ $category->equipment->count() }}</p></div>
            <div><p class="text-xs text-gray-500">{{ __('Image') }}</p><p class="font-medium text-gray-900 dark:text-white">{{ $category->image_url ? __('Available') : __('Not set') }}</p></div>
        </div>
    </x-common.component-card>

    <x-common.component-card :title="__('Equipment in this category')">
        @if($category->equipment->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No equipment found in this category.') }}</p>
        @else
            <div class="overflow-x-auto"><table class="w-full min-w-[760px] text-start text-sm"><thead><tr class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-800"><th class="p-3">{{ __('Equipment') }}</th><th class="p-3">{{ __('Owner') }}</th><th class="p-3">{{ __('Energy') }}</th><th class="p-3">{{ __('Status') }}</th><th class="p-3 text-end">{{ __('Actions') }}</th></tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-800">@foreach($category->equipment as $equipment)<tr><td class="p-3 font-medium text-gray-900 dark:text-white">{{ $equipment->name }}<p class="text-xs text-gray-500">{{ $equipment->brand }} {{ $equipment->model }}</p></td><td class="p-3">{{ $equipment->owner?->name ?? '—' }}</td><td class="p-3">{{ $equipment->energyProfile?->power_watts ?? '—' }} W / {{ $equipment->energyProfile?->capacity_wh ?? '—' }} Wh</td><td class="p-3">{{ __(ucfirst($equipment->status)) }}</td><td class="p-3 text-end"><a href="{{ route('admin.equipment.show', $equipment) }}" class="text-brand-600 hover:underline">{{ __('View') }}</a></td></tr>@endforeach</tbody></table></div>
        @endif
    </x-common.component-card>
</div>
@endsection
