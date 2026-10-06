{{--
    Form shared by create.blade.php and edit.blade.php.
    Variables received from the @include:
      $action      : URL where the form is sent
      $method      : 'POST' to create, 'PUT' to update
      $submitLabel : text of the submit button
      $extension   : the RentalExtension being edited, or null when creating
      $rentals     : rentals that can be extended (only used when creating)
      $selectedRentalId : rental pre-selected through ?rental_id= (only when creating)

    old('field', default) = value typed before a validation error, or the default
    (current database value) the first time the form is shown.
    @error('field') = shows the validation message of that field.
--}}
@php
    // Same CSS classes for every input, to keep the markup short
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 focus:border-brand-400 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300';
@endphp

<form action="{{ $action }}" method="POST" class="space-y-6">
    @csrf
    {{-- Browsers only send GET/POST, so PUT is simulated with a hidden field --}}
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

        {{-- Rental: dropdown when creating, read-only text when editing --}}
        <div class="sm:col-span-2">
            @if ($extension)
                <p class="{{ $labelClass }}">Rental</p>
                <p class="text-sm text-gray-800 dark:text-white">
                    {{ $extension->rental?->reference }} — {{ $extension->rental?->user?->name }}
                    <span class="text-gray-500 dark:text-gray-400">(end date when requested: {{ $extension->old_end_date->format('d/m/Y') }})</span>
                </p>
            @else
                <label for="rental_id" class="{{ $labelClass }}">Rental</label>
                <select id="rental_id" name="rental_id" required class="{{ $inputClass }}">
                    <option value="">Select a rental to extend...</option>
                    @foreach ($rentals as $rental)
                        <option value="{{ $rental->id }}" @selected((string) old('rental_id', $selectedRentalId) === (string) $rental->id)>
                            {{ $rental->reference }} — {{ $rental->user?->name }}
                            (ends {{ $rental->end_date->format('d/m/Y') }})
                        </option>
                    @endforeach
                </select>
                @if ($rentals->isEmpty())
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">No rental can be extended right now (it must be pending or active, without a pending request).</p>
                @endif
                @error('rental_id')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
            @endif
        </div>

        {{-- New end date --}}
        <div>
            <label for="new_end_date" class="{{ $labelClass }}">New end date</label>
            <input id="new_end_date" type="date" name="new_end_date" value="{{ old('new_end_date', $extension?->new_end_date?->format('Y-m-d')) }}" required class="{{ $inputClass }}">
            @error('new_end_date')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
        </div>

        {{-- Additional amount (optional: computed automatically when empty) --}}
        <div>
            <label for="additional_amount" class="{{ $labelClass }}">Additional amount (optional)</label>
            <input id="additional_amount" type="number" step="0.01" min="0" name="additional_amount" value="{{ old('additional_amount', $extension?->additional_amount) }}" class="{{ $inputClass }}">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave empty to compute it from the rental price per day.</p>
            @error('additional_amount')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
        </div>

        {{-- Reason --}}
        <div class="sm:col-span-2">
            <label for="reason" class="{{ $labelClass }}">Reason (optional)</label>
            <textarea id="reason" name="reason" rows="3" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-400 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('reason', $extension?->reason) }}</textarea>
            @error('reason')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- Buttons --}}
    <div class="flex flex-wrap justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
        <a href="{{ route('admin.rental-extensions.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Cancel</a>
        <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">{{ $submitLabel }}</button>
    </div>
</form>