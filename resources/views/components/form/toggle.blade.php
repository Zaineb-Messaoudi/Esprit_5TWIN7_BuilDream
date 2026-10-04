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
    $id = $id ?? 'toggle-' . Str::random(8);
    $disabledClasses = $disabled ? 'cursor-not-allowed opacity-50' : '';
    $classes = trim("{$disabledClasses} {$className}");
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
            class="sr-only"
            x-data="{ checked: @js(old($name) || $value) }"
            @change="checked = !checked"
        />
        <div
            x-bind:class="checked ? 'bg-brand-500 dark:bg-brand-500' : 'bg-gray-200 dark:bg-white/10'"
            class="block h-6 w-11 rounded-full transition-colors duration-200 {{ $classes }}"
        >
        </div>
        <div
            x-bind:class="checked ? 'ltr:translate-x-full rtl:-translate-x-full' : 'translate-x-0'"
            class="shadow-theme-sm absolute top-0.5 ltr:left-0.5 rtl:right-0.5 h-5 w-5 rounded-full bg-white transition-transform duration-200 ease-linear"
        >
        </div>
    </div>
    @if($label)
        <label for="{{ $id }}" class="text-sm font-medium text-gray-700 select-none dark:text-gray-400 cursor-pointer">
            {{ $label }}
        </label>
    @endif
</div>
