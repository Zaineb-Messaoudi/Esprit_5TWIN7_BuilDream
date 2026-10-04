@props([
    'width' => 'full',
    'height' => 'md',
    'className' => '',
])

@php
    $widthClasses = [
        'xs' => 'w-12',
        'sm' => 'w-24',
        'md' => 'w-full',
        'lg' => 'w-full max-w-4xl',
    ];
    $heightClasses = [
        'xs' => 'h-4',
        'sm' => 'h-8',
        'md' => 'h-12',
        'lg' => 'h-24',
    ];
    $wClass = $widthClasses[$width] ?? $widthClasses['md'];
    $hClass = $heightClasses[$height] ?? $heightClasses['md'];
@endphp

<div {{ $attributes->merge(['class' => "animate-pulse rounded-lg bg-gray-200 dark:bg-gray-700 {$wClass} {$hClass} {$className}"]) }}">
</div>
