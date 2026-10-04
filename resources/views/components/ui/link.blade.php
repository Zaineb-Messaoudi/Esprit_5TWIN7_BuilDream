@props([
    'href' => '#',
    'routeName' => null,
    'parameters' => [],
    'variant' => 'primary',
    'external' => false,
])

@php
    $variants = [
        'primary' => 'font-medium text-brand-600 underline-offset-4 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-brand-300',
        'muted' => 'font-medium text-gray-600 underline-offset-4 hover:text-gray-900 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-gray-400 dark:hover:text-white',
        'button' => 'inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2',
    ];
    $destination = $routeName ? route($routeName, $parameters) : $href;
@endphp

<a
    href="{{ $destination }}"
    {{ $attributes->class([$variants[$variant] ?? $variants['primary']]) }}
    @if ($external) target="_blank" rel="noopener noreferrer" @endif
>
    {{ $slot }}
</a>
