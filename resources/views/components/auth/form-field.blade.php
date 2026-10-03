@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'autocomplete' => null,
    'inputmode' => null,
    'required' => false,
    'autofocus' => false,
])

@php
    $hasError = $errors->has($name);
    $errorId = $name.'-error';
@endphp

<div>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
        {{ __($label) }}@if($required)<span class="ms-1 text-error-500" aria-hidden="true">*</span>@endif
    </label>
    <input
        id="{{ $name }}"
        type="{{ $type }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @if($inputmode) inputmode="{{ $inputmode }}" @endif
        @if($required) required @endif
        @if($autofocus) autofocus @endif
        @if($hasError) aria-invalid="true" aria-describedby="{{ $errorId }}" @endif
        @class([
            'h-11 w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-gray-900 shadow-theme-xs outline-none transition placeholder:text-gray-400 focus:ring-3 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30',
            'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-500' => $hasError,
            'border-gray-300 focus:border-brand-400 focus:ring-brand-500/10 dark:border-gray-700 dark:focus:border-brand-400' => !$hasError,
        ])
    />
    @error($name)
        <p id="{{ $errorId }}" class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
    @enderror
</div>
