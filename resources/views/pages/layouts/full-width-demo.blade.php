@extends('layouts.full-width')

@section('content')
    <div class="space-y-6">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Layouts') }} / {{ __('Full width') }}</p>
            <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ __('Full-width layout example') }}</h1>
            <p class="mt-1 max-w-3xl text-sm leading-6 text-gray-500 dark:text-gray-400">{{ __('This responsive shell removes the sidebar and lets wide content use the viewport. It shares the app theme tokens and includes a compact header and skip link.') }}</p>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([__('Content area'), __('Compact header'), __('No sidebar'), __('Responsive width')] as $title)
                <x-common.component-card :title="$title">
                    <p class="text-sm leading-6 text-gray-500 dark:text-gray-400">{{ __('This panel demonstrates the full-width content grid at the current viewport size.') }}</p>
                </x-common.component-card>
            @endforeach
        </div>

        <x-common.component-card :title="__('Full-width content region')" :desc="__('Use this shell for maps, reports, or wide data workspaces.')">
            <div class="flex min-h-52 items-center justify-center rounded-xl border border-dashed border-gray-300 bg-gray-50 text-center dark:border-gray-700 dark:bg-gray-900">
                <div class="max-w-md p-6">
                    <p class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ __('Viewport-aware workspace') }}</p>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('The layout retains responsive page padding while allowing content to expand without a fixed dashboard sidebar.') }}</p>
                </div>
            </div>
        </x-common.component-card>
    </div>
@endsection
