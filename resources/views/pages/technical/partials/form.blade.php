@php
    $fieldClass = 'mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white';
@endphp
<form action="{{ $record ? route($prefix.$resource.'.update', $record) : route($prefix.$resource.'.store') }}" method="POST" class="space-y-5">
    @csrf
    @if ($record) @method('PUT') @endif
    @foreach ($fields as $name => $field)
        @php
            $value = old($name, $record?->$name);
            if ($value instanceof \Carbon\CarbonInterface) $value = $value->format('Y-m-d');
        @endphp
        <div>
            <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __($field['label']) }} @if ($field['required'] ?? false) <span aria-hidden="true">*</span> @endif</label>
            @if ($field['type'] === 'select')
                <select id="{{ $name }}" name="{{ $name }}" class="{{ $fieldClass }}" @if ($field['required'] ?? false) required @endif aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}">
                    <option value="">{{ __('Select an option') }}</option>
                    @foreach ($options[$name] ?? [] as $optionValue => $optionLabel)
                        <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                    @endforeach
                </select>
            @elseif ($field['type'] === 'textarea')
                <textarea id="{{ $name }}" name="{{ $name }}" rows="4" class="{{ $fieldClass }}" @if ($field['required'] ?? false) required @endif aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}">{{ $value }}</textarea>
            @elseif ($field['type'] === 'checkbox')
                <input type="hidden" name="{{ $name }}" value="0">
                <input id="{{ $name }}" type="checkbox" name="{{ $name }}" value="1" @checked((bool) $value) class="mt-2 rounded border-gray-300 text-brand-600 dark:border-gray-700 dark:bg-gray-900" aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}">
            @else
                <input id="{{ $name }}" name="{{ $name }}" type="{{ $field['type'] }}" value="{{ $value }}" @if ($field['type'] === 'number') min="0" step="{{ $field['step'] ?? '1' }}" @endif class="{{ $fieldClass }}" @if ($field['required'] ?? false) required @endif aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}">
            @endif
            @error($name) <p class="mt-1 text-sm text-error-600 dark:text-error-400" role="alert">{{ $message }}</p> @enderror
        </div>
    @endforeach
    <div class="flex flex-wrap gap-3">
        <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2 text-sm font-medium text-white hover:bg-brand-600">{{ $record ? __('Save changes') : __('Create record') }}</button>
        <a href="{{ route($prefix.$resource.'.index') }}" class="rounded-lg border border-gray-300 px-5 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-200">{{ __('Cancel') }}</a>
    </div>
</form>
