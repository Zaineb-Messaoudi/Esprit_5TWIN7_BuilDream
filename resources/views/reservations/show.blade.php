@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-3xl space-y-5 p-6">
    <div class="rounded-xl bg-white p-6 shadow-theme-xs dark:bg-gray-900">
        <h1 class="text-title-lg font-semibold">{{ $reservation->reference }}</h1>
        <p class="mt-3">{{ $reservation->equipment->name }} · {{ $reservation->status }}</p>
        <p>{{ $reservation->start_date->format('Y-m-d') }} → {{ $reservation->end_date->format('Y-m-d') }}</p>
        <div class="mt-5 flex gap-3">
            <a class="button-base button-primary" href="{{ route('rental.reservations.edit',$reservation) }}">{{ __('Edit') }}</a>
            <form method="POST" action="{{ route('rental.reservations.destroy',$reservation) }}" onsubmit="return confirm('{{ __('Delete this reservation?') }}')">@csrf @method('DELETE')<button class="button-base button-danger">{{ __('Delete') }}</button></form>
        </div>
    </div>

    @if ($reservation->cancellation_status === 'requested')
        <section class="rounded-xl bg-white p-6 shadow-theme-xs dark:bg-gray-900">
            <h2 class="text-lg font-semibold">{{ __('Cancellation request') }}</h2>
            <p class="mt-2">{{ __('Reason for cancellation') }}: {{ $reservation->cancellation_reason }}</p>
            <p class="mt-1 text-theme-xs text-gray-500">{{ __('Requested') }}: {{ $reservation->cancellation_requested_at?->format('M j, Y H:i') }}</p>
            <p class="mt-2 text-theme-sm font-semibold">{{ __('Refund policy') }}: {{ number_format((float) $reservation->cancellation_refund_percentage, 0) }}% · {{ __('Refund amount') }}: {{ number_format((float) $reservation->cancellation_refund_total, 2) }} TND</p>
            <div class="mt-3 space-y-2 text-theme-xs">
                @foreach ($reservation->payments()->whereIn('status', ['paid', 'partially_refunded'])->get() as $payment)
                    <p>{{ $payment->transaction_reference }} · {{ __('Refund') }}: {{ number_format((float) $payment->refund_target_amount, 2) }} TND · {{ __('Already refunded') }}: {{ number_format((float) $payment->refunded_amount, 2) }} TND · {{ __(ucfirst(str_replace('_', ' ', $payment->refund_status))) }}</p>
                @endforeach
            </div>
            <form method="POST" action="{{ route('admin.reservations.cancellation-decision', $reservation) }}" class="mt-5 flex flex-wrap items-center gap-3">
                @csrf
                @if ($cancellationRequiresManualRefund)
                    <label class="flex items-start gap-2 text-theme-xs text-gray-600 dark:text-gray-300">
                        <input type="checkbox" name="manual_refund_confirmed" value="1" class="mt-0.5 rounded border-gray-300 @error('manual_refund_confirmed') border-red-500 focus:ring-red-500 @enderror">
                        <span>{{ __('I confirm that the displayed refund for non-Stripe payments has been transferred.') }}</span>
                    </label>
                    @error('manual_refund_confirmed')
                        <x-input-error :messages="[$message]" class="mt-1 ml-6" />
                    @enderror
                @endif
                <button name="decision" value="approve" class="button-base button-primary @error('decision') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror">{{ __('Approve cancellation and refund') }}</button>
                <button name="decision" value="reject" class="min-h-10 rounded-lg border border-gray-300 px-4 text-theme-xs font-semibold dark:border-gray-700 @error('decision') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror">{{ __('Decline request') }}</button>
                @error('decision')
                    <x-input-error :messages="[$message]" class="mt-1 ml-auto" />
                @enderror
            </form>
        </section>
    @endif
</div>
@endsection
