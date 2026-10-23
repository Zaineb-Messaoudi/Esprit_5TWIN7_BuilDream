@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-2xl p-6">
    <h1 class="mb-6 text-title-lg font-semibold">{{ __('New payment') }}</h1>
    <form method="POST" action="{{ route('rental.payments.store') }}" class="space-y-4 rounded-xl bg-white p-6 shadow-theme-xs dark:bg-gray-900">
        @csrf
        <label class="block">
            {{ __('Reservation') }}
            <select name="reservation_id" class="mt-1 w-full rounded-lg border p-2 @error('reservation_id') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror" required>
                @foreach($reservations as $reservation)
                    <option value="{{ $reservation->id }}" @selected(old('reservation_id')==$reservation->id)>{{ $reservation->reference }} — {{ $reservation->equipment->name }}</option>
                @endforeach
            </select>
            @error('reservation_id')
                <x-input-error :messages="[$message]" class="mt-1" />
            @enderror
        </label>
        <label class="block">
            {{ __('Amount') }}
            <input name="amount" type="number" step="0.01" min="0.01" max="99999999.99" value="{{ old('amount') }}" class="mt-1 w-full rounded-lg border p-2 @error('amount') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror" required>
            @error('amount')
                <x-input-error :messages="[$message]" class="mt-1" />
            @enderror
        </label>
        <label class="block">
            {{ __('Payment date') }}
            <input name="payment_date" type="datetime-local" max="{{ now()->format('Y-m-d\TH:i') }}" value="{{ old('payment_date',now()->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-lg border p-2 @error('payment_date') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror" required>
            @error('payment_date')
                <x-input-error :messages="[$message]" class="mt-1" />
            @enderror
        </label>
        <label class="block">
            {{ __('Method') }}
            <select name="payment_method" class="mt-1 w-full rounded-lg border p-2 @error('payment_method') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror" required>
                <option value="CARD" @selected(old('payment_method')==='CARD')>CARD</option>
                <option value="BANK_TRANSFER" @selected(old('payment_method')==='BANK_TRANSFER')>BANK_TRANSFER</option>
                <option value="CASH" @selected(old('payment_method')==='CASH')>CASH</option>
            </select>
            @error('payment_method')
                <x-input-error :messages="[$message]" class="mt-1" />
            @enderror
        </label>
        <input type="hidden" name="status" value="paid">
        <button class="button-base button-primary">{{ __('Pay') }}</button>
    </form>
</div>
@endsection
