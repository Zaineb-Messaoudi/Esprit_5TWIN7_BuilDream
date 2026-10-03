@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Applications') }} / {{ __('Vector Maps') }}</p>
            <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ __('Vector Maps') }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Dedicated vector-map overview using the installed map library and illustrative service locations.') }}</p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            @foreach ([['Global coverage', '42'], ['Service locations', '4'], ['Coverage score', '86%']] as [$label, $value])
                <x-common.component-card :title="__($label)">
                    <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $value }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Illustrative map data') }}</p>
                </x-common.component-card>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <x-common.component-card :title="__('World map')" :desc="__('Pan and zoom across the installed world vector dataset.')" class="xl:col-span-2">
                <div id="mapVectorWorld" data-vector-map class="h-[420px] w-full overflow-hidden rounded-xl bg-gray-50 dark:bg-gray-900" role="img" aria-label="{{ __('World vector map with sample service locations') }}"></div>
                <div class="mt-4 flex flex-wrap gap-4 text-xs text-gray-500 dark:text-gray-400">
                    @foreach ([['bg-brand-500', __('Service locations')], ['bg-success-500', __('Active coverage')], ['bg-warning-500', __('Planned expansion')]] as [$color, $label])
                        <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full {{ $color }}"></span>{{ $label }}</span>
                    @endforeach
                </div>
            </x-common.component-card>

            <div class="space-y-6">
                <x-common.component-card :title="__('Regional sample')" :desc="__('Illustrative service locations in Tunisia.')">
                    <div id="mapVectorRegions" data-vector-map class="h-56 w-full overflow-hidden rounded-xl bg-gray-50 dark:bg-gray-900" role="img" aria-label="{{ __('World vector map with Tunisia service markers') }}"></div>
                </x-common.component-card>
                <x-common.component-card :title="__('Map controls')">
                    <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                        <li class="flex justify-between gap-3"><span>{{ __('Map dataset') }}</span><span class="font-medium">{{ __('World') }}</span></li>
                        <li class="flex justify-between gap-3"><span>{{ __('Zoom buttons') }}</span><span class="font-medium">{{ __('Enabled') }}</span></li>
                        <li class="flex justify-between gap-3"><span>{{ __('Scroll zoom') }}</span><span class="font-medium">{{ __('Disabled') }}</span></li>
                    </ul>
                    <p class="mt-4 border-t border-gray-100 pt-4 text-xs leading-5 text-gray-400 dark:border-gray-800">{{ __('The installed jsVectorMap package supplies world maps only; this page does not claim a separate USA state-level dataset.') }}</p>
                </x-common.component-card>
            </div>
        </div>
    </div>
@endsection
