@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Applications / Map view</p>
                <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">Regional service coverage</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Illustrative locations for the SolarShare demo; markers are not customer addresses.</p>
            </div>
            <label class="sr-only" for="map-region">Filter map region</label>
            <select id="map-region" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option>All regions</option>
                <option>North</option>
                <option>Central</option>
                <option>South</option>
            </select>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-common.component-card title="Active service zones">
                <p class="text-2xl font-semibold text-gray-800 dark:text-white">12</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Across 4 regions</p>
            </x-common.component-card>
            <x-common.component-card title="Installations this month">
                <p class="text-2xl font-semibold text-gray-800 dark:text-white">186</p>
                <p class="mt-1 text-xs text-success-600">+9.4% from last month</p>
            </x-common.component-card>
            <x-common.component-card title="Scheduled visits">
                <p class="text-2xl font-semibold text-gray-800 dark:text-white">24</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Next 7 days</p>
            </x-common.component-card>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <x-common.component-card title="Service region map" desc="Illustrative network view · not to geographic scale" class="xl:col-span-2">
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900">
                    <svg viewBox="0 0 720 420" class="h-auto min-h-72 w-full" role="img" aria-labelledby="service-map-title service-map-description">
                        <title id="service-map-title">SolarShare service region locations</title>
                        <desc id="service-map-description">A schematic network diagram showing service locations in Tunis, Sousse, Sfax, and Gabes. It is not geographically accurate.</desc>
                        <defs>
                            <pattern id="map-grid" width="32" height="32" patternUnits="userSpaceOnUse">
                                <path d="M 32 0 L 0 0 0 32" fill="none" stroke="currentColor" stroke-width="1" class="text-gray-200 dark:text-gray-800" />
                            </pattern>
                        </defs>
                        <rect width="720" height="420" fill="url(#map-grid)" />
                        <path d="M156 110 C245 65 306 134 376 150 S512 124 567 206 S482 304 395 274 S240 324 164 258 S116 160 156 110Z" fill="none" stroke="currentColor" stroke-width="3" stroke-dasharray="8 8" class="text-brand-300 dark:text-brand-700" />
                        <path d="M165 165 L320 125 L455 202 L350 270 L495 298" fill="none" stroke="currentColor" stroke-width="2" class="text-brand-400 dark:text-brand-600" />
                        @foreach ([['Tunis', 165, 165], ['Sousse', 320, 125], ['Sfax', 455, 202], ['Gabes', 495, 298]] as [$city, $x, $y])
                            <g>
                                <circle cx="{{ $x }}" cy="{{ $y }}" r="18" fill="currentColor" class="text-brand-500/15" />
                                <circle cx="{{ $x }}" cy="{{ $y }}" r="7" fill="currentColor" class="text-brand-600" />
                                <text x="{{ $x + 14 }}" y="{{ $y - 14 }}" class="fill-gray-700 dark:fill-gray-200" font-size="14" font-weight="600">{{ $city }}</text>
                            </g>
                        @endforeach
                        <text x="24" y="394" class="fill-gray-400" font-size="12">Schematic coverage view · sample data</text>
                    </svg>
                </div>
            </x-common.component-card>

            <x-common.component-card title="Regional distribution" desc="Demo installation totals by region">
                <div class="space-y-5">
                    @foreach ([['North', 72, '1,248'], ['Central', 54, '936'], ['Coastal', 43, '742'], ['South', 28, '486']] as [$region, $percent, $count])
                        <div>
                            <div class="mb-2 flex items-center justify-between text-sm">
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $region }}</span>
                                <span class="text-gray-500 dark:text-gray-400">{{ $count }}</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800" role="progressbar" aria-label="{{ $region }} installations" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
                                <div class="h-full rounded-full bg-brand-500" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-common.component-card>
        </div>
    </div>
@endsection
