@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('UI Elements') }} / {{ __('Ribbons') }}</p>
            <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ __('Ribbons') }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Reusable corner labels for highlighting status, featured content, and notices.') }}</p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <x-ui.ribbon :label="__('Featured')" color="brand">
                <h2 class="text-sm font-semibold text-gray-800 dark:text-white">{{ __('Solar panel bundle') }}</h2>
                <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ __('A solid ribbon highlights a featured item without changing the surrounding card layout.') }}</p>
            </x-ui.ribbon>
            <x-ui.ribbon :label="__('Active')" color="success" variant="light" position="top-start">
                <h2 class="text-sm font-semibold text-gray-800 dark:text-white">{{ __('Regional installation plan') }}</h2>
                <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ __('Light ribbons work well for operational states and small labels.') }}</p>
            </x-ui.ribbon>
            <x-ui.ribbon :label="__('Review')" color="warning" variant="outline" position="bottom-end">
                <h2 class="text-sm font-semibold text-gray-800 dark:text-white">{{ __('Pending approval') }}</h2>
                <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ __('Outlined ribbons keep the visual weight low for secondary status.') }}</p>
            </x-ui.ribbon>
            <x-ui.ribbon :label="__('Needs attention')" color="error" variant="light" position="bottom-start">
                <h2 class="text-sm font-semibold text-gray-800 dark:text-white">{{ __('Connection health') }}</h2>
                <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ __('Choose position, tone, and appearance through component properties.') }}</p>
            </x-ui.ribbon>
        </div>
    </div>
@endsection
