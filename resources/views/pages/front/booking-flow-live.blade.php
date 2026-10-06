@extends('layouts.front')

@section('content')
    <section class="bg-gray-950 text-white">
        <div class="mx-auto max-w-7xl px-4 py-9 sm:px-8 lg:px-10">
            <p class="text-theme-xs font-semibold uppercase tracking-[0.2em] text-warning-300">{{ __('Equipment reservation') }}</p>
            <h1 class="mt-2 text-title-sm font-semibold sm:text-title-md">{{ __($title) }}</h1>
            <p class="mt-2 text-theme-sm text-gray-300">{{ $item->name }} · {{ number_format((float) $item->price_per_day, 2) }} TND / {{ __('day') }}</p>
        </div>
    </section>

    <main class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-8 lg:px-10">
        @if (session('status'))
            <div role="status" class="rounded-xl border border-success-200 bg-success-50 p-4 text-theme-sm text-success-800 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-200">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-error-200 bg-error-50 p-4 text-theme-sm text-error-800 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-200">{{ $errors->first() }}</div>
        @endif

        @if ($slug === 'reserve')
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Choose your rental dates') }}</h2>
                <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('The owner will review your request. The final total includes 19% tax.') }}</p>
                <form method="POST" action="{{ route('booking.reservations.store') }}" class="mt-6 grid gap-4 sm:grid-cols-2">
                    @csrf
                    <input type="hidden" name="equipment_id" value="{{ $item->id }}">
                    <label class="text-theme-sm text-gray-700 dark:text-gray-300">{{ __('Start date') }}<input required type="date" min="{{ now()->toDateString() }}" name="start_date" value="{{ old('start_date', $startDate) }}" class="mt-1.5 min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></label>
                    <label class="text-theme-sm text-gray-700 dark:text-gray-300">{{ __('End date') }}<input required type="date" min="{{ now()->toDateString() }}" name="end_date" value="{{ old('end_date', $endDate) }}" class="mt-1.5 min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></label>
                    <p class="sm:col-span-2 text-theme-xs text-gray-500">{{ __('Availability is checked again when you submit the request.') }}</p>
                    <button class="button-base button-primary min-h-11 px-5 text-theme-sm sm:col-span-2">{{ __('Send reservation request') }}</button>
                </form>
            </section>
        @else
            @php($subtotal = (float) $reservation->total_amount)
            @php($tax = round($subtotal * 0.19, 2))
            @php($paid = $reservation->payments->firstWhere('status', 'paid'))
            @php($pendingPayment = $reservation->payments->firstWhere('status', 'pending'))
            @if ($slug === 'invoice')
                <section class="mx-auto max-w-3xl rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-9">
                    <div class="flex flex-wrap items-start justify-between gap-4 border-b border-gray-100 pb-5 dark:border-gray-800"><div><p class="text-theme-xs font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-300">{{ __('SolarShare invoice') }}</p><h2 class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ $reservation->invoice->invoice_number }}</h2><p class="mt-1 text-theme-xs text-gray-500">{{ $reservation->reference }} · {{ $reservation->invoice->issue_date->format('M j, Y') }}</p></div><button type="button" onclick="window.print()" class="min-h-10 rounded-lg border border-gray-300 px-4 text-theme-xs font-semibold dark:border-gray-700">{{ __('Print / save as PDF') }}</button></div>
                    <p class="py-6 font-semibold text-gray-900 dark:text-white">{{ $item->name }} · {{ $reservation->start_date->format('M j, Y') }} – {{ $reservation->end_date->format('M j, Y') }}</p>
                    <dl class="space-y-3 text-theme-sm"><div class="flex justify-between"><dt>{{ __('Subtotal') }}</dt><dd>{{ number_format((float) $reservation->invoice->subtotal, 2) }} TND</dd></div><div class="flex justify-between"><dt>{{ __('Tax · 19%') }}</dt><dd>{{ number_format((float) $reservation->invoice->tax, 2) }} TND</dd></div><div class="flex justify-between border-t border-gray-200 pt-3 font-semibold dark:border-gray-700"><dt>{{ __('Total') }}</dt><dd>{{ number_format((float) $reservation->invoice->total, 2) }} TND</dd></div><div class="flex justify-between"><dt>{{ __('Status') }}</dt><dd>{{ __(ucfirst($reservation->invoice->status)) }}</dd></div></dl>
                </section>
            @else
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div><p class="text-theme-xs uppercase tracking-wide text-gray-500">{{ __('Reservation reference') }}</p><h2 class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $reservation->reference }}</h2></div>
                    <span class="rounded-full bg-warning-50 px-3 py-1 text-theme-xs font-semibold text-warning-800 dark:bg-warning-500/15 dark:text-warning-300">{{ __(ucfirst($reservation->status)) }}</span>
                </div>
                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    @foreach ([[__('Equipment'), $item->name], [__('Start date'), $reservation->start_date->format('M j, Y')], [__('End date'), $reservation->end_date->format('M j, Y')], [__('Owner'), $item->owner], [__('Subtotal'), number_format($subtotal, 2).' TND'], [__('Tax · 19%'), number_format($tax, 2).' TND']] as [$label, $value])
                        <div class="rounded-xl bg-gray-50 p-4 dark:bg-white/5"><dt class="text-theme-xs text-gray-500">{{ $label }}</dt><dd class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $value }}</dd></div>
                    @endforeach
                </dl>
                <p class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">{{ __('Total') }}: {{ number_format($subtotal + $tax, 2) }} TND</p>

                @if (in_array($slug, ['booking-summary', 'payment'], true) && $reservation->status === 'confirmed' && ! $paid && ! $pendingPayment)
                    <form method="POST" action="{{ route('booking.reservations.pay', $reservation) }}" class="mt-6 flex flex-wrap items-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                        @csrf
                        <label class="min-w-52 flex-1 text-theme-sm text-gray-700 dark:text-gray-300">{{ __('Payment method') }}
                            <select name="payment_method" required class="mt-1.5 min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 dark:border-gray-700 dark:bg-gray-900 dark:text-white"><option value="CARD">{{ __('Card') }}</option><option value="BANK_TRANSFER">{{ __('Bank transfer') }}</option><option value="CASH">{{ __('Cash on pickup') }}</option></select>
                        </label>
                        <button class="button-base button-primary min-h-11 px-5 text-theme-sm">{{ __('Record payment method') }}</button>
                    </form>
                    <p class="mt-3 text-theme-xs text-gray-500">{{ __('This app records the payment request; it does not charge a card. An administrator must verify and mark payment as paid.') }}</p>
                @elseif ($reservation->status === 'pending')
                    <p class="mt-5 rounded-xl bg-warning-50 p-4 text-theme-sm text-warning-900 dark:bg-warning-500/10 dark:text-warning-200">{{ __('Waiting for the equipment owner to review this request.') }}</p>
                @elseif ($reservation->status === 'cancelled')
                    <p class="mt-5 rounded-xl bg-gray-50 p-4 text-theme-sm text-gray-600 dark:bg-white/5 dark:text-gray-300">{{ __('This reservation was cancelled or declined.') }}</p>
                @elseif ($paid)
                    <p class="mt-5 rounded-xl bg-success-50 p-4 text-theme-sm text-success-800 dark:bg-success-500/10 dark:text-success-200">{{ __('Payment verified. Your rental, contract, and invoice are available in your account.') }}</p>
                    @if ($reservation->invoice)<a class="mt-4 inline-flex button-base button-primary min-h-11 items-center px-5 text-theme-sm" href="{{ route('front.invoice', ['equipment' => $item->id, 'reservation' => $reservation->id]) }}">{{ __('View invoice') }}</a>@endif
                @elseif ($pendingPayment)
                    <p class="mt-5 rounded-xl bg-warning-50 p-4 text-theme-sm text-warning-900 dark:bg-warning-500/10 dark:text-warning-200">{{ __('Payment is awaiting administrator verification. You will find the rental and invoice here after it is marked paid.') }}</p>
                @else
                    <p class="mt-5 rounded-xl bg-brand-50 p-4 text-theme-sm text-brand-900 dark:bg-brand-500/10 dark:text-brand-100">{{ __('The owner has approved your request. You can now record a payment method.') }}</p>
                    <a href="{{ route('front.booking-summary', ['equipment' => $item->id, 'reservation' => $reservation->id]) }}" class="mt-4 inline-flex button-base button-primary min-h-11 items-center px-5 text-theme-sm">{{ __('Continue to payment') }}</a>
                @endif
            </section>

            @if ($slug === 'confirmed')
                <section class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]"><h2 class="font-semibold text-gray-900 dark:text-white">{{ __('Payment records') }}</h2>
                    @forelse ($reservation->payments as $payment)
                        <div class="mt-3 flex flex-wrap justify-between gap-2 border-t border-gray-100 pt-3 text-theme-sm dark:border-gray-800"><span>{{ $payment->transaction_reference }} · {{ __(str_replace('_', ' ', ucfirst(strtolower($payment->payment_method)))) }}</span><span>{{ number_format((float) $payment->amount, 2) }} TND · {{ __(ucfirst($payment->status)) }}</span></div>
                    @empty <p class="mt-3 text-theme-sm text-gray-500">{{ __('No payment has been recorded yet.') }}</p> @endforelse
                </section>
            @endif
            @endif
        @endif
    </main>
@endsection
