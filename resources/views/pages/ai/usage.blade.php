@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('AI Studio') }} / {{ __('Usage') }}</p>
                <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Usage analytics page example · no provider is connected and no tokens have been consumed.') }}</p>
            </div>
            <x-ui.badge color="warning" variant="light">{{ __('Demo data only') }}</x-ui.badge>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                [__('Requests this month'), __('0 live'), __('No connected provider')],
                [__('Tokens processed'), __('0 live'), __('No prompts sent')],
                [__('Images generated'), __('0 live'), __('No generation service')],
                [__('Estimated spend'), '$0.00', __('No billable activity')],
            ] as [$label, $value, $note])
                <x-common.component-card :title="$label">
                    <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $value }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $note }}</p>
                </x-common.component-card>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <x-common.component-card :title="__('Monthly usage limit')" :desc="__('Illustrative plan quota; actual usage remains zero.')">
                <x-ui.progress :value="0" :label="__('Requests used')" />
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">{{ __('0 of 10,000 demo requests') }}</p>
            </x-common.component-card>
            <x-common.component-card :title="__('Usage by tool')" :desc="__('No tool usage is recorded in this UI-only build.')">
                <div class="space-y-4">
                    @foreach ([__('Text'), __('Images'), __('Video'), __('Code')] as $tool)
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-sm text-gray-600 dark:text-gray-300">{{ $tool }}</span>
                            <x-ui.tag>{{ __('No live usage') }}</x-ui.tag>
                        </div>
                    @endforeach
                </div>
            </x-common.component-card>
            <x-common.component-card :title="__('Provider status')" :desc="__('Connections are not configured in this demonstration.')">
                <div class="flex items-center gap-3">
                    <span class="h-2.5 w-2.5 rounded-full bg-gray-400" aria-hidden="true"></span>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('No AI provider connected') }}</p>
                </div>
                <a href="{{ route('ai.settings') }}" class="mt-5 inline-flex text-sm font-medium text-brand-600 hover:underline dark:text-brand-300">{{ __('View AI settings') }}</a>
            </x-common.component-card>
        </div>

        <x-common.component-card :title="__('Recent usage')" :desc="__('The table remains empty until an approved AI provider is integrated.')">
            <x-ui.empty-state
                :title="__('No usage to report')"
                :message="__('No prompts, generated content, or provider requests are recorded in this demo.')"
            />
        </x-common.component-card>
    </div>
@endsection
