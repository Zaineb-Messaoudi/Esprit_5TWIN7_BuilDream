{{--
    Form shared by create.blade.php and edit.blade.php.
    Variables received from the @include:
      $action      : URL where the form is sent
      $method      : 'POST' to create, 'PUT' to update
      $submitLabel : text of the submit button
      $contract    : the RentalContract being edited, or null when creating
      $rentals     : rentals WITHOUT a contract (only used when creating)
      $selectedRentalId : rental pre-selected through ?rental_id= (only when creating)

    old('field', default) = value typed before a validation error, or the default
    (current database value) the first time the form is shown.
    @error('field') = shows the validation message of that field.
--}}
@php
    // Same CSS classes for every input, to keep the markup short
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 focus:border-brand-400 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300';

    // Default contract text proposed when creating a new contract
    $defaultTerms = 'The renter agrees to use the equipment with care, to return it on the end date of the rental and in the same condition as received. Any damage or loss is the responsibility of the renter.';
@endphp

<form action="{{ $action }}" method="POST" class="space-y-6">
    @csrf
    {{-- Browsers only send GET/POST, so PUT is simulated with a hidden field --}}
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

        {{-- Rental: a dropdown when creating, read-only text when editing (a contract cannot change rental) --}}
        <div class="sm:col-span-2">
            @if ($contract)
                <p class="{{ $labelClass }}">Rental</p>
                <p class="text-sm text-gray-800 dark:text-white">
                    {{ $contract->rental?->reference }} — {{ $contract->rental?->user?->name }}
                    <span class="text-gray-500 dark:text-gray-400">(contract {{ $contract->contract_number }})</span>
                </p>
            @else
                <label for="rental_id" class="{{ $labelClass }}">Rental</label>
                <select id="rental_id" name="rental_id" required class="{{ $inputClass }}">
                    <option value="">Select a rental without contract...</option>
                    @foreach ($rentals as $rental)
                        <option value="{{ $rental->id }}" @selected((string) old('rental_id', $selectedRentalId) === (string) $rental->id)>
                            {{ $rental->reference }} — {{ $rental->user?->name }}
                            ({{ $rental->start_date->format('d/m/Y') }} → {{ $rental->end_date->format('d/m/Y') }})
                        </option>
                    @endforeach
                </select>
                @if ($rentals->isEmpty())
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Every rental already has a contract. Create a rental first.</p>
                @endif
                @error('rental_id')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
            @endif
        </div>

        {{-- Deposit --}}
        <div>
            <label for="deposit_amount" class="{{ $labelClass }}">Deposit amount</label>
            <input id="deposit_amount" type="number" step="0.01" min="0" name="deposit_amount" value="{{ old('deposit_amount', $contract?->deposit_amount ?? 0) }}" required class="{{ $inputClass }}">
            @error('deposit_amount')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
        </div>

        {{-- Contract status (options come from the ContractStatus enum) --}}
        <div>
            <label for="contract_status" class="{{ $labelClass }}">Status</label>
            <select id="contract_status" name="contract_status" required class="{{ $inputClass }}">
                @foreach (\App\Enums\ContractStatus::options() as $value => $label)
                    <option value="{{ $value }}" @selected(old('contract_status', $contract?->contract_status?->value ?? 'draft') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('contract_status')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
        </div>

        {{-- Signature date: optional --}}
        <div class="sm:col-span-2">
            <label for="signed_at" class="{{ $labelClass }}">Signed at (optional)</label>
            <input id="signed_at" type="datetime-local" name="signed_at" value="{{ old('signed_at', $contract?->signed_at?->format('Y-m-d\TH:i')) }}" class="{{ $inputClass }} sm:max-w-xs">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave empty to use the current date when the status is Signed. Ignored for a Draft.</p>
            @error('signed_at')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
        </div>

        {{-- Terms and conditions --}}
        <div class="sm:col-span-2">
            <label for="terms" class="{{ $labelClass }}">Terms and conditions</label>
            <textarea id="terms" name="terms" rows="5" required class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-400 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('terms', $contract?->terms ?? $defaultTerms) }}</textarea>
            @error('terms')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- Buttons --}}
    <div class="flex flex-wrap justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
        <a href="{{ route('admin.rental-contracts.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Cancel</a>
        <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">{{ $submitLabel }}</button>
    </div>
</form>