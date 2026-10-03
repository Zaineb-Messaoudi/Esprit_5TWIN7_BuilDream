@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Charts') }} / {{ $title }}</p>
            <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $description }} {{ __('All values are illustrative.') }}</p>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            @foreach ($examples as $example)
                <x-common.component-card :title="$example['title']" :desc="$example['description']">
                    <x-charts.apex :options="$example['options']" :height="$example['height'] ?? 320" :label="$example['title']" />
                </x-common.component-card>
            @endforeach
        </div>
    </div>
@endsection
