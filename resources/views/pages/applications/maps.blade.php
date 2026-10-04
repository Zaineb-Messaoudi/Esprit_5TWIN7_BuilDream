@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Applications') }} / {{ __('Maps') }}</p>
            <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ __('Map examples') }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Vector map examples use illustrative public locations and do not identify customer addresses.') }}</p>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <x-common.component-card :title="__('Basic vector map')" :desc="__('Explore country boundaries and zoom controls.')">
                <div id="mapBasic" data-vector-map class="h-72 w-full overflow-hidden rounded-xl bg-gray-50 dark:bg-gray-900" role="img" aria-label="{{ __('World map without markers') }}"></div>
            </x-common.component-card>
            <x-common.component-card :title="__('Single marker')" :desc="__('A focused map with one sample location.')">
                <div id="mapMarker" data-vector-map class="h-72 w-full overflow-hidden rounded-xl bg-gray-50 dark:bg-gray-900" role="img" aria-label="{{ __('World map with one marker in Tunis') }}"></div>
            </x-common.component-card>
            <x-common.component-card :title="__('Multiple markers')" :desc="__('Compare multiple illustrative service locations.')">
                <div id="mapMultiple" data-vector-map class="h-72 w-full overflow-hidden rounded-xl bg-gray-50 dark:bg-gray-900" role="img" aria-label="{{ __('World map with multiple Tunisia markers') }}"></div>
            </x-common.component-card>
            <x-common.component-card :title="__('Custom markers')" :desc="__('Different marker sizes and colors help distinguish location groups.')">
                <div id="mapCustom" data-vector-map class="h-72 w-full overflow-hidden rounded-xl bg-gray-50 dark:bg-gray-900" role="img" aria-label="{{ __('World map with custom location markers') }}"></div>
            </x-common.component-card>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <x-common.component-card :title="__('Location information')" :desc="__('Illustrative regional service locations.')">
                <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ([['Tunis', __('North'), '1,248'], ['Sousse', __('Coast'), '936'], ['Sfax', __('Central coast'), '742'], ['Gabes', __('South coast'), '486']] as [$city, $region, $total])
                        <li class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $city }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $region }}</p>
                            </div>
                            <x-ui.badge color="primary" variant="light">{{ $total }} {{ __('installations') }}</x-ui.badge>
                        </li>
                    @endforeach
                </ul>
            </x-common.component-card>
            <x-common.component-card :title="__('Geographic statistics')" :desc="__('Sample distribution by service region.')">
                <div class="space-y-5">
                    @foreach ([['North', 72], ['Coast', 54], ['Central coast', 43], ['South coast', 28]] as [$region, $percent])
                        <x-ui.progress :value="$percent" :label="__($region)" />
                    @endforeach
                </div>
            </x-common.component-card>
            <x-common.component-card :title="__('Map legend')" :desc="__('Sample marker groups used in this map library.')">
                <ul class="space-y-4 text-sm text-gray-600 dark:text-gray-300">
                    <li class="flex items-center gap-3"><span class="h-3 w-3 rounded-full bg-brand-500"></span>{{ __('Primary service hub') }}</li>
                    <li class="flex items-center gap-3"><span class="h-3 w-3 rounded-full bg-success-500"></span>{{ __('Active coverage') }}</li>
                    <li class="flex items-center gap-3"><span class="h-3 w-3 rounded-full bg-warning-500"></span>{{ __('Planned expansion') }}</li>
                    <li class="flex items-center gap-3"><span class="h-3 w-3 rounded-full bg-theme-purple-500"></span>{{ __('Partner location') }}</li>
                </ul>
            </x-common.component-card>
        </div>
    </div>
@endsection
