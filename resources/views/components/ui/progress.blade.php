@props([
    'value' => 0,
    'max' => 100,
    'color' => 'primary',
    'size' => 'md',
    'label' => null,
])

@php
    $safeMax = max(1, (float) $max);
    $percentage = min(100, max(0, (float) $value / $safeMax * 100));
    $colors = [
        'primary' => 'bg-brand-500',
        'success' => 'bg-success-500',
        'warning' => 'bg-warning-500',
        'error' => 'bg-error-500',
    ];
    $sizes = [
        'sm' => 'h-1.5',
        'md' => 'h-2.5',
        'lg' => 'h-4',
    ];
@endphp

<div {{ $attributes }}>
    @if ($label)
        <div class="mb-2 flex items-center justify-between gap-3 text-sm">
            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $label }}</span>
            <span class="text-gray-500 dark:text-gray-400">{{ (int) round($percentage) }}%</span>
        </div>
    @endif
    <div
        class="w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800 {{ $sizes[$size] ?? $sizes['md'] }}"
        role="progressbar"
        aria-valuenow="{{ (int) round($percentage) }}"
        aria-valuemin="0"
        aria-valuemax="100"
        @if ($label) aria-label="{{ $label }}" @endif
    >
        <div class="h-full rounded-full transition-all {{ $colors[$color] ?? $colors['primary'] }}" style="width: {{ $percentage }}%"></div>
    </div>
</div>
