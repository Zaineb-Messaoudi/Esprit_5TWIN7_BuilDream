@props([
    'label' => null,
    'size' => 'md',
])

@php
    $sizes = [
        'sm' => 'h-4 w-4',
        'md' => 'h-5 w-5',
        'lg' => 'h-6 w-6',
    ];
@endphp

<span
    {{ $attributes->class(['inline-flex shrink-0 items-center justify-center', $sizes[$size] ?? $sizes['md']]) }}
    @if ($label) role="img" aria-label="{{ $label }}" @else aria-hidden="true" @endif
>
    {{ $slot }}
</span>
