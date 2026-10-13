{{--
    Form shared by create.blade.php and edit.blade.php.
    Variables received from the @include:
      $action      : URL where the form is sent
      $method      : 'POST' to create, 'PUT' to update
      $submitLabel : text of the submit button
      $rental      : the Rental being edited, or null when creating
      $users       : list of users for the "Renter" dropdown
      $equipments  : list of equipment (empty until Student 1's model is merged)

    old('field', default) = the value typed before a validation error,
    or the default (the current database value) the first time the form is shown.
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

        {{-- Renter --}}
        <div>
            <label for="user_id" class="{{ $labelClass }}">Renter</label>
            <select id="user_id" name="user_id" required class="{{ $inputClass }}">
                <option value="">Select a user...</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected((string) old('user_id', $rental?->user_id) === (string) $user->id)>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                @endforeach
            </select>
            @error('user_id')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
        </div>

        {{-- Equipment: dropdown when the Equipment model exists, plain number otherwise --}}
        <div>
            <label for="equipment_id" class="{{ $labelClass }}">Equipment</label>
            @if ($equipments->isNotEmpty())
                <select id="equipment_id" name="equipment_id" required class="{{ $inputClass }}">
                    <option value="">Select equipment...</option>
                    @foreach ($equipments as $equipment)
                        <option value="{{ $equipment->id }}" @selected((string) old('equipment_id', $rental?->equipment_id) === (string) $equipment->id)>
                            {{ $equipment->name }}
                        </option>
                    @endforeach
                </select>
            @else
                <input id="equipment_id" type="number" min="1" name="equipment_id" value="{{ old('equipment_id', $rental?->equipment_id) }}" required placeholder="Equipment id" class="{{ $inputClass }}">
            @endif
            @error('equipment_id')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
        </div>

        {{-- Start date --}}
        <div>
            <label for="start_date" class="{{ $labelClass }}">Start date</label>
            <input id="start_date" type="date" name="start_date" value="{{ old('start_date', $rental?->start_date?->format('Y-m-d')) }}" required class="{{ $inputClass }}">
            @error('start_date')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
        </div>

        {{-- End date --}}
        <div>
            <label for="end_date" class="{{ $labelClass }}">End date</label>
            <input id="end_date" type="date" name="end_date" value="{{ old('end_date', $rental?->end_date?->format('Y-m-d')) }}" required class="{{ $inputClass }}">
            @error('end_date')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
        </div>

        {{-- Total amount --}}
        <div>
            <label for="total_amount" class="{{ $labelClass }}">Total amount</label>
            <input id="total_amount" type="number" step="0.01" min="0" name="total_amount" value="{{ old('total_amount', $rental?->total_amount) }}" required class="{{ $inputClass }}">
            @error('total_amount')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
        </div>

        {{-- Status (options come from the RentalStatus enum) --}}
        <div>
            <label for="status" class="{{ $labelClass }}">Status</label>
            <select id="status" name="status" required class="{{ $inputClass }}">
                @foreach (\App\Enums\RentalStatus::options() as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $rental?->status?->value ?? 'pending') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
        </div>

        {{-- Reservation (optional) --}}
        <div>
            <label for="reservation_id" class="{{ $labelClass }}">Reservation id (optional)</label>
            <input id="reservation_id" type="number" min="1" name="reservation_id" value="{{ old('reservation_id', $rental?->reservation_id) }}" class="{{ $inputClass }}">
            @error('reservation_id')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- Buttons --}}
    <div class="flex flex-wrap justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
        <a href="{{ route('admin.rentals.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Cancel</a>
        <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">{{ $submitLabel }}</button>
    </div>
</form>