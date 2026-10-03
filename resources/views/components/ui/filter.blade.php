@props([
    'name' => 'filter',
    'label' => __('Filter'),
    'options' => [],
    'value' => '',
])

@php
    $selectId = $attributes->get('id', 'filter-' . \Illuminate\Support\Str::uuid());
@endphp

<div {{ $attributes->except('id')->class(['min-w-40']) }}>
    <label for="{{ $selectId }}" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
        {{ $label }}
    </label>
    <select
        id="{{ $selectId }}"
        name="{{ $name }}"
        {{ $attributes->only(['disabled', 'required', 'aria-describedby'])->class([
            'w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 shadow-theme-xs',
            'focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10',
            'disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-400',
            'dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:disabled:bg-gray-800',
        ]) }}
    >
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>
</div>
