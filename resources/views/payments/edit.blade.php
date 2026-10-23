@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-xl p-6">
    <h1 class="mb-6 text-title-lg font-semibold">{{ __('Edit payment') }}</h1>
    <form method="POST" action="{{ route('rental.payments.update',$payment) }}" class="space-y-4 rounded-xl bg-white p-6 shadow-theme-xs dark:bg-gray-900">
        @csrf @method('PUT')
        <label class="block">
            {{ __('Status') }}
            <select name="status" class="w-full rounded-lg border p-2 @error('status') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror">
                <option @selected($payment->status==='pending')>pending</option>
                <option @selected($payment->status==='paid')>paid</option>
                <option @selected($payment->status==='failed')>failed</option>
            </select>
            @error('status')
                <x-input-error :messages="[$message]" class="mt-1" />
            @enderror
        </label>
        <button class="button-base button-primary">{{ __('Update') }}</button>
    </form>
</div>
@endsection
