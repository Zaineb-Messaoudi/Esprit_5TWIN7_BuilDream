@extends('layouts.front')

@php
    $equipment = $record->equipment;
    $reservation = $recordType === 'reservation' ? $record : $record->reservation;
@endphp

@section('content')
    <section class="bg-gray-950 text-white"><div class="mx-auto max-w-7xl px-4 py-9 sm:px-8 lg:px-10"><p class="text-theme-xs font-semibold uppercase tracking-[0.2em] text-warning-300">{{ $owner ? __('Owner workspace') : __('Renter workspace') }}</p><h1 class="mt-2 text-title-sm font-semibold sm:text-title-md">{{ $title }}</h1></div></section>
    @if ($owner) @include('pages.front.partials.owner-nav') @else @include('pages.front.partials.buyer-nav') @endif
    <main class="mx-auto max-w-4xl space-y-5 px-4 py-8 sm:px-8 lg:px-10">
        @if (session('status'))<div role="status" class="rounded-xl border border-success-200 bg-success-50 p-4 text-theme-sm text-success-800 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-200">{{ session('status') }}</div>@endif
        @if ($recordType === 'reservation')
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex flex-wrap items-start justify-between gap-4"><div><p class="text-theme-xs uppercase tracking-wide text-gray-500">{{ __('Reservation reference') }}</p><h2 class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ $record->reference }}</h2></div><span class="rounded-full bg-warning-50 px-3 py-1 text-theme-xs font-semibold text-warning-800 dark:bg-warning-500/15 dark:text-warning-300">{{ __(ucfirst($record->status)) }}</span></div>
                <dl class="mt-6 grid gap-4 sm:grid-cols-2">@foreach ([[__('Equipment'), $equipment?->name], [$owner ? __('Renter') : __('Owner'), $owner ? $record->user?->name : $equipment?->owner?->name], [__('Start date'), $record->start_date->format('M j, Y')], [__('End date'), $record->end_date->format('M j, Y')], [__('Subtotal'), number_format((float) $record->total_amount, 2).' TND'], [__('Total with tax'), number_format((float) $record->total_amount * 1.19, 2).' TND']] as [$label, $value])<div class="rounded-xl bg-gray-50 p-4 dark:bg-white/5"><dt class="text-theme-xs text-gray-500">{{ $label }}</dt><dd class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $value }}</dd></div>@endforeach</dl>
                @if ($owner && $record->status === 'pending')<div class="mt-6 flex gap-3"><form method="POST" action="{{ route('owner.reservations.decision', $record) }}">@csrf<input type="hidden" name="decision" value="approve"><button class="button-base button-primary min-h-10 px-4 text-theme-xs">{{ __('Approve request') }}</button></form><form method="POST" action="{{ route('owner.reservations.decision', $record) }}">@csrf<input type="hidden" name="decision" value="reject"><button class="min-h-10 rounded-lg border border-error-300 px-4 text-theme-xs font-semibold text-error-700 dark:border-error-500/40 dark:text-error-300">{{ __('Decline') }}</button></form></div>@endif
                @if (! $owner && $record->status === 'confirmed')<a href="{{ route('front.booking-summary', ['equipment' => $equipment->id, 'reservation' => $record->id]) }}" class="mt-6 inline-flex button-base button-primary min-h-10 items-center px-4 text-theme-xs">{{ __('Continue reservation') }}</a>@endif
            </section>
        @else
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]"><div class="flex flex-wrap items-start justify-between gap-4"><div><p class="text-theme-xs uppercase tracking-wide text-gray-500">{{ __('Rental reference') }}</p><h2 class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ $record->reference }}</h2></div><span class="rounded-full bg-brand-50 px-3 py-1 text-theme-xs font-semibold text-brand-800 dark:bg-brand-500/15 dark:text-brand-300">{{ __($record->status->label()) }}</span></div>
                <dl class="mt-6 grid gap-4 sm:grid-cols-2">@foreach ([[__('Equipment'), $equipment?->name], [__('Renter'), $record->user?->name], [__('Start date'), $record->start_date->format('M j, Y')], [__('End date'), $record->end_date->format('M j, Y')], [__('Total'), number_format((float) $record->total_amount, 2).' TND'], [__('Contract'), $record->contract?->contract_number ?? __('Pending')]] as [$label, $value])<div class="rounded-xl bg-gray-50 p-4 dark:bg-white/5"><dt class="text-theme-xs text-gray-500">{{ $label }}</dt><dd class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $value }}</dd></div>@endforeach</dl>
                @if ($record->contract)<div class="mt-5 rounded-xl border border-gray-200 p-4 dark:border-gray-800"><h3 class="font-semibold text-gray-900 dark:text-white">{{ __('Rental terms') }}</h3><p class="mt-2 text-theme-sm text-gray-600 dark:text-gray-300">{{ $record->contract->terms }}</p>@if (! $owner && $record->contract->contract_status->value === 'draft')<form method="POST" action="{{ route('buyer.contracts.sign', $record->contract) }}" class="mt-4">@csrf<button class="button-base button-primary min-h-10 px-4 text-theme-xs">{{ __('Sign contract') }}</button></form>@endif</div>@endif
                @if ($reservation?->invoice)<a href="{{ route('front.invoice', ['equipment' => $equipment->id, 'reservation' => $reservation->id]) }}" class="mt-5 inline-flex button-base button-secondary min-h-10 items-center px-4 text-theme-xs">{{ __('View invoice') }}</a>@endif
            </section>
        @endif
    </main>
@endsection
