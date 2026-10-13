<div class="space-y-5">
    <div>
        <label for="equipment_id" class="block">{{ __('Equipment') }}</label>
        <select id="equipment_id" name="equipment_id" class="mt-1 w-full rounded-lg border p-2" required>
            <option value="">{{ __('Select equipment') }}</option>
            @foreach($equipments as $equipment)
                <option value="{{ $equipment->id }}" @selected(old('equipment_id', $reservation->equipment_id ?? '') == $equipment->id)>{{ $equipment->name }}</option>
            @endforeach
        </select>
        @error('equipment_id')<p class="text-error-600 text-sm">{{ $message }}</p>@enderror
    </div>
    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="reservation-start-date" class="block">{{ __('Start date') }}</label>
            <input data-date-picker data-min-date="{{ now()->toDateString() }}" data-paired-date="#reservation-end-date" id="reservation-start-date" type="text" name="start_date" value="{{ old('start_date', isset($reservation) ? $reservation->start_date->format('Y-m-d') : '') }}" class="mt-1 w-full rounded-lg border p-2" required>
            @error('start_date')<p class="text-error-600 text-sm">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="reservation-end-date" class="block">{{ __('End date') }}</label>
            <input data-date-picker id="reservation-end-date" type="text" name="end_date" value="{{ old('end_date', isset($reservation) ? $reservation->end_date->format('Y-m-d') : '') }}" class="mt-1 w-full rounded-lg border p-2" required>
            @error('end_date')<p class="text-error-600 text-sm">{{ $message }}</p>@enderror
        </div>
    </div>
    <p class="text-theme-xs text-gray-500">{{ __('Choose dates from the calendar. The server recalculates the total and rejects overlapping reservations.') }}</p>
    @if (isset($reservation) && auth()->user()->isAdmin())
        <div>
            <label for="status" class="block">{{ __('Reservation status') }}</label>
            <select id="status" name="status" class="mt-1 w-full rounded-lg border p-2">
                @foreach(['pending', 'confirmed', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $reservation->status) === $status)>{{ __(ucfirst($status)) }}</option>
                @endforeach
            </select>
            @error('status')<p class="text-error-600 text-sm">{{ $message }}</p>@enderror
        </div>
    @endif
</div>
