@props([
    'options' => [],
    'height' => 280,
    'label' => __('Chart'),
    'loading' => false,
    'empty' => false,
])

@if ($loading)
    <div class="space-y-4" role="status" aria-label="{{ __('Loading chart') }}" aria-busy="true">
        <x-ui.skeleton height="sm" width="sm" />
        <x-ui.skeleton height="lg" class="w-full" />
        <span class="sr-only">{{ __('Loading chart') }}</span>
    </div>
@elseif ($empty)
    <x-ui.empty-state :title="__('No chart data')" :message="__('Data will appear here when it is available.')" />
@else
    <div
        x-data="apexChart(@js($options), {{ (int) $height }})"
        x-init="mount($refs.chart)"
        @theme-changed.window="syncTheme($event.detail)"
        class="min-w-0"
    >
        <div x-ref="chart" role="img" aria-label="{{ $label }}" class="min-w-0"></div>
    </div>
@endif
