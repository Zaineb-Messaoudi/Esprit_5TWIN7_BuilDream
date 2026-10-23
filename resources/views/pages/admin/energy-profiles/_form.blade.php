<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @foreach([['power_watts', 'Power (W)', 'number'], ['voltage', 'Voltage (V)', 'number'], ['capacity_wh', 'Capacity (Wh)', 'number'], ['efficiency', 'Efficiency (%)', 'number'], ['technology', 'Technology', 'text'], ['max_output', 'Max output (W)', 'number'], ['operating_duration', 'Operating duration (h)', 'number']] as [$key, $label, $type])
        <div>
            <label for="{{ $key }}" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __($label) }}</label>
            <input id="{{ $key }}" name="{{ $key }}" type="{{ $type }}" step="0.01" value="{{ old($key, $profile->{$key} ?? '') }}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white @error($key) border-red-500 focus:ring-red-500 focus:border-red-500 @enderror">
            @error($key)
                <x-input-error :messages="[$message]" class="mt-1" />
            @enderror
        </div>
    @endforeach
</div>
