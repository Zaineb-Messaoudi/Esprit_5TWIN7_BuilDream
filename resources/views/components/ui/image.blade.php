@props([
    'src',
    'alt',
    'ratio' => 'square',
])

@php
    $ratios = [
        'square' => 'aspect-square',
        'video' => 'aspect-video',
        'portrait' => 'aspect-[3/4]',
        'auto' => '',
    ];
@endphp

<img
    src="{{ $src }}"
    alt="{{ $alt }}"
    loading="lazy"
    decoding="async"
    {{ $attributes->class([
        'block h-auto w-full object-cover',
        $ratios[$ratio] ?? $ratios['square'],
    ]) }}
/>
