@props([
    'variant' => 'success',
    'title',
    'message',
])

@php
    $variants = [
        'success' => 'border-success-200 bg-success-50 text-success-800 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-300',
        'error' => 'border-error-200 bg-error-50 text-error-800 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-300',
        'warning' => 'border-warning-200 bg-warning-50 text-warning-800 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-300',
        'info' => 'border-blue-light-200 bg-blue-light-50 text-blue-light-800 dark:border-blue-light-500/30 dark:bg-blue-light-500/10 dark:text-blue-light-300',
    ];
@endphp

<div x-data="{ visible: true }" x-show="visible" x-transition role="{{ $variant === 'error' ? 'alert' : 'status' }}" aria-live="{{ $variant === 'error' ? 'assertive' : 'polite' }}" class="flex items-start gap-3 rounded-xl border p-4 {{ $variants[$variant] ?? $variants['info'] }}">
    <span class="mt-0.5 flex-1">
        <span class="block text-sm font-semibold">{{ $title }}</span>
        <span class="mt-1 block text-sm opacity-80">{{ $message }}</span>
    </span>
    <button type="button" @click="visible = false" aria-label="{{ __('Dismiss notification') }}" class="rounded p-1 opacity-70 hover:bg-black/5 hover:opacity-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-current dark:hover:bg-white/10">
        <span aria-hidden="true">×</span>
    </button>
</div>
