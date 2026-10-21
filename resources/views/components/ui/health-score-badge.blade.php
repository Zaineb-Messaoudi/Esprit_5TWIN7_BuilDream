@props(['health'])

@php
    $classes = match ($health['level']) {
        'healthy' => 'bg-success-50 text-success-700 ring-success-600/20 dark:bg-success-500/15 dark:text-success-400',
        'warning' => 'bg-warning-50 text-warning-700 ring-warning-600/20 dark:bg-warning-500/15 dark:text-warning-400',
        default => 'bg-error-50 text-error-700 ring-error-600/20 dark:bg-error-500/15 dark:text-error-400',
    };
    $label = match ($health['level']) {
        'healthy' => __('Healthy'),
        'warning' => __('Needs attention'),
        default => __('Critical'),
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-theme-xs font-semibold ring-1 ring-inset', $classes]) }}>
    <span class="size-2 rounded-full bg-current" aria-hidden="true"></span>
    {{ __('Health score') }}: {{ $health['score'] }}/100 · {{ $label }}
</span>
