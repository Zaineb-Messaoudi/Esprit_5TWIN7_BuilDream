@props([
    'variant' => 'light',
    'size' => 'md',
    'color' => 'primary',
    'startIcon' => null,
    'endIcon' => null,
])

@php
    $baseStyles = 'inline-flex items-center px-2.5 py-0.5 justify-center gap-1 rounded-full font-medium capitalize';

    $sizeStyles = [
        'xs' => 'text-[10px]',
        'sm' => 'text-xs',
        'md' => 'text-sm',
        'lg' => 'text-base',
    ];

    $variants = [
        'light' => [
            'primary' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400',
            'success' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
            'error' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
            'warning' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-orange-400',
            'info' => 'bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/15 dark:text-blue-light-500',
            'gray' => 'bg-gray-100 text-gray-700 dark:bg-white/5 dark:text-white/80',
            'dark' => 'bg-gray-800 text-white dark:bg-gray-700 dark:text-white',
        ],
        'solid' => [
            'primary' => 'bg-brand-500 text-white dark:text-white',
            'success' => 'bg-success-500 text-white dark:text-white',
            'error' => 'bg-error-500 text-white dark:text-white',
            'warning' => 'bg-warning-500 text-white dark:text-white',
            'info' => 'bg-blue-light-500 text-white dark:text-white',
            'gray' => 'bg-gray-400 text-white dark:bg-gray-700 dark:text-white',
            'dark' => 'bg-gray-900 text-white dark:bg-black dark:text-white',
        ],
    ];

    $sizeClass = $sizeStyles[$size] ?? $sizeStyles['md'];
    $colorStyles = $variants[$variant][$color] ?? $variants['light']['primary'];
@endphp

<span class="{{ $baseStyles }} {{ $sizeClass }} {{ $colorStyles }}" {{ $attributes }}>
    @if($startIcon)
        <span class="shrink-0">{!! $startIcon !!}</span>
    @endif

    {{ $slot }}

    @if($endIcon)
        <span class="shrink-0">{!! $endIcon !!}</span>
    @endif
</span>
