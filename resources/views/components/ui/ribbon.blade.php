@props([
    'label' => __('Featured'),
    'color' => 'brand',
    'position' => 'top-end',
    'variant' => 'solid',
])

@php
    $positionClasses = [
        'top-start' => 'start-4 top-4',
        'top-end' => 'end-4 top-4',
        'bottom-start' => 'bottom-4 start-4',
        'bottom-end' => 'bottom-4 end-4',
    ];
    $colorClasses = [
        'brand' => ['solid' => 'bg-brand-500 text-white', 'light' => 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300', 'outline' => 'border-brand-500 text-brand-600 dark:text-brand-300'],
        'success' => ['solid' => 'bg-success-500 text-white', 'light' => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-300', 'outline' => 'border-success-500 text-success-600 dark:text-success-300'],
        'warning' => ['solid' => 'bg-warning-500 text-white', 'light' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-300', 'outline' => 'border-warning-500 text-warning-700 dark:text-warning-300'],
        'error' => ['solid' => 'bg-error-500 text-white', 'light' => 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-300', 'outline' => 'border-error-500 text-error-600 dark:text-error-300'],
    ];
    $positionClass = $positionClasses[$position] ?? $positionClasses['top-end'];
    $tone = $colorClasses[$color] ?? $colorClasses['brand'];
    $appearance = $tone[$variant] ?? $tone['solid'];
@endphp

<div {{ $attributes->class(['relative rounded-xl border border-gray-200 bg-white p-5 pt-14 dark:border-gray-800 dark:bg-gray-900']) }}>
    <span class="absolute {{ $positionClass }} inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold shadow-theme-xs {{ $appearance }} {{ $variant === 'outline' ? 'bg-white dark:bg-gray-900' : 'border-transparent' }}">
        {{ $label }}
    </span>
    {{ $slot }}
</div>
