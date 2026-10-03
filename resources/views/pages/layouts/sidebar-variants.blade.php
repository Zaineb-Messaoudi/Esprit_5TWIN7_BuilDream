@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Layouts') }} / {{ __('Sidebar Variants') }}</p>
            <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ __('Sidebar Variants') }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Six reusable navigation patterns shown as previews; the active application sidebar remains unchanged.') }}</p>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 2xl:grid-cols-3">
            @foreach (['classic', 'sectioned', 'documentation', 'collapsible', 'nested', 'toggle'] as $variant)
                <x-layouts.sidebar-variant-preview :variant="$variant" />
            @endforeach
        </div>
    </div>
@endsection
