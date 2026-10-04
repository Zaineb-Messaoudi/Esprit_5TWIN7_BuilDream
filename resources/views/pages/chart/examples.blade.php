@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Charts') }} / {{ __('Examples') }}</p>
            <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Interactive ApexCharts examples with responsive sizing, tooltips, legends, and theme-aware colors. All figures are illustrative.') }}</p>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            @foreach ($examples as $example)
                <x-common.component-card :title="$example['title']" :desc="$example['description']">
                    @if (isset($example['metric']))
                        <div class="mb-1 flex items-end gap-3">
                            <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $example['metric'] }}</p>
                            <x-ui.badge color="success" variant="light">{{ $example['change'] }}</x-ui.badge>
                        </div>
                    @endif
                    <x-charts.apex
                        :options="$example['options']"
                        :height="$example['height'] ?? 280"
                        :label="$example['title']"
                    />
                </x-common.component-card>
            @endforeach
        </div>
    </div>
@endsection
