@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-2xl p-6">
    <h1 class="mb-6 text-title-lg font-semibold">{{ __('New invoice') }}</h1>
    <form method="POST" action="{{ route('rental.invoices.store') }}" class="space-y-4 rounded-xl bg-white p-6 shadow-theme-xs dark:bg-gray-900">
        @csrf
        <label class="block">
            {{ __('Reservation') }}
            <select name="reservation_id" class="mt-1 w-full rounded-lg border p-2 @error('reservation_id') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror" required>
                @foreach($reservations as $reservation)
                    <option value="{{ $reservation->id }}" @selected(old('reservation_id') == $reservation->id)>{{ $reservation->reference }}</option>
                @endforeach
            </select>
            @error('reservation_id')
                <x-input-error :messages="[$message]" class="mt-1" />
            @enderror
        </label>
        <label class="block">
            {{ __('Issue date') }}
            <input type="date" name="issue_date" value="{{ old('issue_date',now()->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border p-2 @error('issue_date') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror" required>
            @error('issue_date')
                <x-input-error :messages="[$message]" class="mt-1" />
            @enderror
        </label>
        <label class="block">
            {{ __('Subtotal') }}
            <input type="number" step="0.01" name="subtotal" value="{{ old('subtotal') }}" class="mt-1 w-full rounded-lg border p-2 @error('subtotal') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror" required>
            @error('subtotal')
                <x-input-error :messages="[$message]" class="mt-1" />
            @enderror
        </label>
        <input type="hidden" name="status" value="unpaid">
        <button class="button-base button-primary">{{ __('Create') }}</button>
    </form>
</div>
@endsection
