@props([
    'label' => '',
    'name' => '',
    'options' => [],
    'selected' => '',
    'required' => false,
    'disabled' => false,
    'className' => '',
])

@php
    $baseClasses = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none ltr:pl-4 ltr:pr-11 rtl:pr-4 rtl:pl-11 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $disabledClasses = $disabled ? 'cursor-not-allowed opacity-50 bg-gray-50 dark:bg-gray-800' : '';
    $classes = trim("{$baseClasses} {$disabledClasses} {$className}");
@endphp

<div class="relative z-20 bg-transparent w-full">
    @if($label)
        <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            {{ $label }}
        </label>
    @endif
    <div class="relative">
        <select
            name="{{ $name }}"
            id="{{ $name }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            class="{{ $classes }}"
            {{ $attributes }}
        >
            @foreach($options as $value => $label)
                <option value="{{ $value }}" {{ $selected == $value ? 'selected' : '' }} class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <span class="pointer-events-none absolute top-1/2 ltr:right-4 rtl:left-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
            <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </span>
    </div>
</div>
