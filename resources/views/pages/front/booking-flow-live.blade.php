@extends('layouts.front')

@section('content')
    <section class="bg-gray-950 text-white">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-5 px-4 py-9 sm:px-8 lg:px-10">
            <div>
                <p class="text-theme-xs font-semibold uppercase tracking-[0.2em] text-warning-300">{{ __('Equipment reservation') }}</p>
                <h1 class="mt-2 text-title-sm font-semibold sm:text-title-md">{{ __($title) }}</h1>
                <p class="mt-2 text-theme-sm text-gray-300">{{ $item->name }} · {{ number_format((float) $item->price_per_day, 2) }} TND / {{ __('day') }}</p>
            </div>
            <a href="{{ route('ai.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-white/25 bg-white/10 px-4 py-2 text-theme-sm font-semibold text-white transition-colors hover:bg-white/20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-warning-300">
                {{ __('AI Dashboard') }}
            </a>
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
                <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('The owner will review your request. The final total includes :rate% tax.', ['rate' => number_format((float) config('billing.tax_rate', 0.19) * 100, 2)]) }}</p>
                <form method="POST" action="{{ route('booking.reservations.store') }}" class="mt-6 grid gap-4 sm:grid-cols-2" x-data="{ startDate: @js(old('start_date', $startDate)) }">
                    @csrf
                    <input type="hidden" name="equipment_id" value="{{ $item->id }}">
                    <label class="text-theme-sm text-gray-700 dark:text-gray-300">{{ __('Start date') }}<input required type="date" min="{{ now()->toDateString() }}" x-model="startDate" name="start_date" value="{{ old('start_date', $startDate) }}" class="mt-1.5 min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></label>
                    <label class="text-theme-sm text-gray-700 dark:text-gray-300">{{ __('End date') }}<input required type="date" min="{{ now()->toDateString() }}" :min="startDate || '{{ now()->toDateString() }}'" name="end_date" value="{{ old('end_date', $endDate) }}" class="mt-1.5 min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></label>
                    <p class="sm:col-span-2 text-theme-xs text-gray-500">{{ __('Availability is checked again when you submit the request.') }}</p>
                    <button class="button-base button-primary min-h-11 px-5 text-theme-sm sm:col-span-2">{{ __('Send reservation request') }}</button>
                </form>
            </section>
        @else
            @php($subtotal = (float) $reservation->total_amount)
            @php($tax = round($subtotal * (float) config('billing.tax_rate', 0.19), 2))
            @php($totalDue = $reservation->invoice?->total ?? $paymentPlan->totalWithTax($reservation))
            @php($amountPaid = $paymentPlan->amountPaid($reservation))
            @php($remainingAmount = $paymentPlan->remainingAmount($reservation))
            @php($nextPaymentAmount = $paymentPlan->nextPaymentAmount($reservation))
            @php($nextPaymentStage = $paymentPlan->nextPaymentStage($reservation))
            @php($nextPaymentDueDate = $paymentPlan->nextPaymentDueDate($reservation))
            @php($pendingPayment = $reservation->payments->firstWhere('status', 'pending'))
            @if ($slug === 'invoice')
                <section class="mx-auto max-w-3xl rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-9">
                    <div class="flex flex-wrap items-start justify-between gap-4 border-b border-gray-100 pb-5 dark:border-gray-800"><div><p class="text-theme-xs font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-300">{{ __('SolarShare invoice') }}</p><h2 class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ $reservation->invoice->invoice_number }}</h2><p class="mt-1 text-theme-xs text-gray-500">{{ $reservation->reference }} · {{ $reservation->invoice->issue_date->format('M j, Y') }}</p></div><button type="button" onclick="window.print()" class="min-h-10 rounded-lg border border-gray-300 px-4 text-theme-xs font-semibold dark:border-gray-700">{{ __('Print / save as PDF') }}</button></div>
                    <p class="py-6 font-semibold text-gray-900 dark:text-white">{{ $item->name }} · {{ $reservation->start_date->format('M j, Y') }} – {{ $reservation->end_date->format('M j, Y') }}</p>
                    <dl class="space-y-3 text-theme-sm"><div class="flex justify-between"><dt>{{ __('Subtotal') }}</dt><dd>{{ number_format((float) $reservation->invoice->subtotal, 2) }} TND</dd></div><div class="flex justify-between"><dt>{{ __('Tax (:rate%)', ['rate' => number_format((float) $reservation->invoice->subtotal > 0 ? (float) $reservation->invoice->tax / (float) $reservation->invoice->subtotal * 100 : 0, 2)]) }}</dt><dd>{{ number_format((float) $reservation->invoice->tax, 2) }} TND</dd></div><div class="flex justify-between border-t border-gray-200 pt-3 font-semibold dark:border-gray-700"><dt>{{ __('Total') }}</dt><dd>{{ number_format((float) $reservation->invoice->total, 2) }} TND</dd></div><div class="flex justify-between"><dt>{{ __('Amount paid') }}</dt><dd>{{ number_format($amountPaid, 2) }} TND</dd></div><div class="flex justify-between"><dt>{{ __('Remaining balance') }}</dt><dd>{{ number_format($remainingAmount, 2) }} TND</dd></div><div class="flex justify-between"><dt>{{ __('Status') }}</dt><dd>{{ __(ucfirst(str_replace('_', ' ', $reservation->invoice->status))) }}</dd></div></dl>
                </section>
            @else
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div><p class="text-theme-xs uppercase tracking-wide text-gray-500">{{ __('Reservation reference') }}</p><h2 class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $reservation->reference }}</h2></div>
                    <span class="rounded-full bg-warning-50 px-3 py-1 text-theme-xs font-semibold text-warning-800 dark:bg-warning-500/15 dark:text-warning-300">{{ __(ucfirst($reservation->status)) }}</span>
                </div>
                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    @foreach ([[__('Equipment'), $item->name], [__('Start date'), $reservation->start_date->format('M j, Y')], [__('End date'), $reservation->end_date->format('M j, Y')], [__('Owner'), $item->owner], [__('Subtotal'), number_format($subtotal, 2).' TND'], [__('Tax (:rate%)', ['rate' => number_format((float) config('billing.tax_rate', 0.19) * 100, 2)]), number_format($tax, 2).' TND']] as [$label, $value])
                        <div class="rounded-xl bg-gray-50 p-4 dark:bg-white/5"><dt class="text-theme-xs text-gray-500">{{ $label }}</dt><dd class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $value }}</dd></div>
                    @endforeach
                </dl>
                <p class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">{{ __('Total') }}: {{ number_format($totalDue, 2) }} TND</p>
                @if ($reservation->status === 'confirmed' && $remainingAmount > 0)
                    <div class="mt-4 rounded-xl bg-brand-50 p-4 text-theme-sm text-brand-900 dark:bg-brand-500/10 dark:text-brand-100">
                        <p>{{ __('Amount paid') }}: {{ number_format($amountPaid, 2) }} TND · {{ __('Remaining balance') }}: {{ number_format($remainingAmount, 2) }} TND</p>
                        @if ($nextPaymentStage === 'deposit')
                            <p class="mt-1">{{ __('Pay a 30% deposit of :amount TND now. The remaining balance is due by :date.', ['amount' => number_format($nextPaymentAmount, 2), 'date' => \Illuminate\Support\Carbon::parse($reservation->start_date)->subDays(7)->format('M j, Y')]) }}</p>
                        @elseif ($nextPaymentStage === 'full')
                            <p class="mt-1">{{ __('Your rental starts within 7 days, so the full :amount TND is due now.', ['amount' => number_format($nextPaymentAmount, 2)]) }}</p>
                        @else
                            <p class="mt-1">{{ __('Your remaining balance of :amount TND is due by :date. You may pay it earlier.', ['amount' => number_format($nextPaymentAmount, 2), 'date' => \Illuminate\Support\Carbon::parse($nextPaymentDueDate)->format('M j, Y')]) }}</p>
                        @endif
                    </div>
                @endif

                @if ($reservation->status === 'confirmed' && $remainingAmount > 0 && (filled(config('services.stripe.secret')) || ! $pendingPayment) && ! ($slug === 'confirmed' && $pendingPayment && str_starts_with($pendingPayment->transaction_reference, 'cs_')))
                    <form method="POST" action="{{ route('booking.reservations.pay', $reservation, false) }}" class="mt-6 flex flex-wrap items-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                        @csrf
                        @if (filled(config('services.stripe.secret')))
                            <input type="hidden" name="payment_method" value="CARD">
                            <div class="flex-1 text-theme-sm text-gray-700 dark:text-gray-300">
                                <p class="font-semibold">{{ __('Pay securely by card with Stripe') }}</p>
                                <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Next payment') }}: {{ number_format($nextPaymentAmount, 2) }} TND. {{ __('You will be redirected to Stripe to complete payment. You can retry if a previous checkout was cancelled.') }}</p>
                            </div>
                            <button class="button-base button-primary min-h-11 px-5 text-theme-sm">{{ __('Pay now') }}</button>
                        @else
                            <label class="min-w-52 flex-1 text-theme-sm text-gray-700 dark:text-gray-300">{{ __('Payment method') }}
                                <select name="payment_method" required class="mt-1.5 min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 dark:border-gray-700 dark:bg-gray-900 dark:text-white"><option value="BANK_TRANSFER">{{ __('Bank transfer') }}</option><option value="CASH">{{ __('Cash on pickup') }}</option></select>
                            </label>
                            <button class="button-base button-primary min-h-11 px-5 text-theme-sm">{{ __('Record payment method') }}</button>
                            <p class="basis-full text-theme-xs text-warning-700 dark:text-warning-300">{{ __('Stripe is not configured, so online card payment is unavailable. Configure Stripe to enable it.') }}</p>
                        @endif
                    </form>
                @elseif ($reservation->status === 'pending')
                    <p class="mt-5 rounded-xl bg-warning-50 p-4 text-theme-sm text-warning-900 dark:bg-warning-500/10 dark:text-warning-200">{{ __('Waiting for the equipment owner to review this request.') }}</p>
                @elseif ($reservation->status === 'cancelled')
                    <p class="mt-5 rounded-xl bg-gray-50 p-4 text-theme-sm text-gray-600 dark:bg-white/5 dark:text-gray-300">{{ __('This reservation was cancelled or declined.') }}</p>
                @elseif ($remainingAmount <= 0)
                    <p class="mt-5 rounded-xl bg-success-50 p-4 text-theme-sm text-success-800 dark:bg-success-500/10 dark:text-success-200">{{ __('Payment verified. Your rental, contract, and invoice are available in your account.') }}</p>
                    @if ($reservation->invoice)<a class="mt-4 inline-flex button-base button-primary min-h-11 items-center px-5 text-theme-sm" href="{{ route('front.invoice', ['equipment' => $item->id, 'reservation' => $reservation->id]) }}">{{ __('View invoice') }}</a>@endif
                @elseif ($pendingPayment && str_starts_with($pendingPayment->transaction_reference, 'cs_'))
                    <p class="mt-5 rounded-xl bg-warning-50 p-4 text-theme-sm text-warning-900 dark:bg-warning-500/10 dark:text-warning-200">{{ __('Stripe is confirming your payment. This page will update once the verified webhook arrives.') }}</p>
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
