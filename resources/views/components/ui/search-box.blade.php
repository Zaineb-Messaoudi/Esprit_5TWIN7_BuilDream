@props([
    'name' => 'search',
    'id' => null,
    'label' => null,
    'placeholder' => null,
])

@php
    $inputId = $id ?? 'search-' . \Illuminate\Support\Str::uuid();
@endphp

<div>
    <label for="{{ $inputId }}" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label ?? __('Search') }}</label>
    <div class="relative">
        <svg class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <circle cx="8.75" cy="8.75" r="5.75" stroke="currentColor" stroke-width="1.5"></circle>
            <path d="m13 13 4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
        </svg>
        <input
            id="{{ $inputId }}"
            type="search"
            name="{{ $name }}"
            placeholder="{{ $placeholder }}"
            {{ $attributes->class(['w-full rounded-lg border border-gray-300 bg-white py-2.5 ps-10 pe-3 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500']) }}
        >
    </div>
</div>
