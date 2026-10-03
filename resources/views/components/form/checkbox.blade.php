@props([
    'id' => null,
    'label' => '',
    'name' => '',
    'value' => '',
    'required' => false,
    'disabled' => false,
    'className' => '',
])

@php
    $id = $id ?? 'checkbox-' . Str::random(8);
    $baseClasses = 'sr-only';
    $disabledClasses = $disabled ? 'cursor-not-allowed opacity-50' : '';
    $classes = trim("{$baseClasses} {$disabledClasses} {$className}");
@endphp

<div class="flex items-center gap-3">
    <div class="relative">
        <input
            type="checkbox"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ $value }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            class="{{ $classes }}"
            x-data="{ checked: @js(old($name) || $value) }"
            @change="checked = !checked"
        />
        <div
            x-bind:class="checked ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
            class="hover:border-brand-500 dark:hover:border-brand-500 ltr:mr-3 rtl:ml-3 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] transition-colors duration-200"
        >
            <span x-show="checked" class="text-white">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="currentColor" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </span>
        </div>
    </div>
    @if($label)
        <label for="{{ $id }}" class="text-sm font-medium text-gray-700 select-none dark:text-gray-400 cursor-pointer">
            {{ $label }}
        </label>
    @endif
</div>
