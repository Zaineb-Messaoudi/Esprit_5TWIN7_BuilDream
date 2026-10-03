@props([
    'color' => 'gray',
    'removable' => false,
    'label' => null,
])

@php
    $colors = [
        'primary' => 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300',
        'success' => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-300',
        'warning' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-300',
        'error' => 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-300',
        'gray' => 'bg-gray-100 text-gray-700 dark:bg-white/5 dark:text-gray-300',
    ];
@endphp

<span
    @if ($removable) x-data="{ visible: true }" x-show="visible" @endif
    {{ $attributes->class(['inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium', $colors[$color] ?? $colors['gray']]) }}
>
    {{ $slot }}
    @if ($removable)
        <button type="button" @click="visible = false" aria-label="{{ $label ?? __('Remove tag') }}" class="-me-1 inline-flex h-4 w-4 items-center justify-center rounded hover:bg-black/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-current dark:hover:bg-white/10">
            <span aria-hidden="true">×</span>
        </button>
    @endif
</span>
