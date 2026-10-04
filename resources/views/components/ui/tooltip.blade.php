@props([
    'text',
    'position' => 'top',
    'id' => null,
])

@php
    $tooltipId = $id ?? 'tooltip-' . \Illuminate\Support\Str::uuid();
    $positions = [
        'top' => 'bottom-full start-1/2 mb-2 -translate-x-1/2',
        'bottom' => 'top-full start-1/2 mt-2 -translate-x-1/2',
        'start' => 'end-full top-1/2 me-2 -translate-y-1/2',
        'end' => 'start-full top-1/2 ms-2 -translate-y-1/2',
    ];
@endphp

<span class="group relative inline-flex">
    {{ $slot }}
    <span
        id="{{ $tooltipId }}"
        role="tooltip"
        class="pointer-events-none invisible absolute z-50 w-max max-w-56 rounded-lg bg-gray-900 px-3 py-2 text-xs font-medium text-white opacity-0 shadow-theme-lg transition group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100 {{ $positions[$position] ?? $positions['top'] }}"
    >{{ $text }}</span>
</span>
