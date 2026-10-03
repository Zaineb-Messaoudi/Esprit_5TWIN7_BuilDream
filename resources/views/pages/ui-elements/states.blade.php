@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">UI Elements / {{ $title }}</p>
            <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Reusable interface states for responsive admin workflows.</p>
        </div>

        @if ($type === 'skeletons')
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <x-common.component-card title="Profile loading state">
                    <div class="flex items-center gap-4">
                        <x-ui.skeleton width="sm" height="lg" class="rounded-full" />
                        <div class="flex-1 space-y-3">
                            <x-ui.skeleton width="lg" height="sm" />
                            <x-ui.skeleton width="md" height="xs" />
                        </div>
                    </div>
                    <div class="mt-6 space-y-3">
                        <x-ui.skeleton height="sm" />
                        <x-ui.skeleton height="sm" />
                        <x-ui.skeleton width="lg" height="sm" />
                    </div>
                </x-common.component-card>
                <x-common.component-card title="Metric card loading state">
                    <x-ui.skeleton width="sm" height="xs" />
                    <x-ui.skeleton width="md" height="lg" class="mt-4" />
                    <x-ui.skeleton width="sm" height="xs" class="mt-4" />
                    <span class="sr-only">Loading dashboard metric</span>
                </x-common.component-card>
            </div>
        @elseif ($type === 'empty-state')
            <x-common.component-card title="No records yet">
                <x-ui.empty-state
                    title="Your workspace is ready"
                    message="When new activity arrives, the latest records and updates will appear here."
                />
            </x-common.component-card>
            <x-common.component-card title="No search results">
                <x-ui.empty-state
                    title="No matching results"
                    message="Try another keyword or clear your filters to see more records."
                />
            </x-common.component-card>
        @else
            <x-common.component-card title="Confirmation dialog">
                <div x-data="{ open: false }" class="space-y-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Confirm destructive actions before applying changes to customer or inventory records.</p>
                    <button type="button" @click="open = true" class="rounded-lg bg-error-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-error-600 focus:outline-hidden focus:ring-2 focus:ring-error-500 focus:ring-offset-2">Open confirmation</button>
                    <div x-show="open" x-cloak @keydown.escape.window="open = false" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4" role="dialog" aria-modal="true" aria-labelledby="confirmation-title">
                        <div @click.outside="open = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-lg dark:bg-gray-900">
                            <h2 id="confirmation-title" class="text-lg font-semibold text-gray-800 dark:text-white">Remove this item?</h2>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">This demo action will not change or delete any application data.</p>
                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button" @click="open = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Cancel</button>
                                <button type="button" @click="open = false" class="rounded-lg bg-error-500 px-4 py-2 text-sm font-medium text-white hover:bg-error-600">Confirm</button>
                            </div>
                        </div>
                    </div>
                </div>
            </x-common.component-card>
        @endif
    </div>
@endsection
