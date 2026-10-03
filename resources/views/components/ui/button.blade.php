@props([
    'variant' => 'primary',
    'size' => 'md',
    'startIcon' => null,
    'endIcon' => null,
    'className' => '',
    'disabled' => false,
])

@php
    $base = 'inline-flex items-center justify-center font-medium gap-2 rounded-lg transition duration-200';

    $sizeMap = [
        'xs' => 'px-2 py-1.5 text-xs',
        'sm' => 'px-3 py-2 text-sm',
        'md' => 'px-4 py-2.5 text-sm',
        'lg' => 'px-5 py-3 text-base',
    ];
    $sizeClass = $sizeMap[$size] ?? $sizeMap['md'];

    $variantMap = [
        'primary' => 'bg-brand-500 text-white shadow-theme-xs hover:bg-brand-600 disabled:bg-brand-300',
        'secondary' => 'bg-gray-800 text-white hover:bg-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600 disabled:bg-gray-300',
        'outline' => 'bg-white text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03] dark:hover:text-gray-300',
        'ghost' => 'bg-transparent text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-300',
        'success' => 'bg-success-500 text-white hover:bg-success-600 disabled:bg-success-300',
        'error' => 'bg-error-500 text-white hover:bg-error-600 disabled:bg-error-300',
        'warning' => 'bg-warning-500 text-white hover:bg-warning-600 disabled:bg-warning-300',
    ];
    $variantClass = $variantMap[$variant] ?? $variantMap['primary'];

    $disabledClass = $disabled ? 'cursor-not-allowed opacity-50' : '';

    $classes = trim("{$base} {$sizeClass} {$variantClass} {$className} {$disabledClass}");
@endphp

<button
    {{ $attributes->merge(['class' => $classes, 'type' => $attributes->get('type', 'button')]) }}
    @if($disabled) disabled @endif
>
    @if($startIcon)
        <span class="flex items-center shrink-0">{!! $startIcon !!}</span>
    @endif

    {{ $slot }}

    @if($endIcon)
        <span class="flex items-center shrink-0">{!! $endIcon !!}</span>
    @endif
</button>
