@extends('layouts.front')

@php
    $reservations = [
        ['reference' => 'RES-1042', 'equipment' => 'Portable battery 1000 Wh', 'dates' => '9–11 Oct 2026', 'start' => '2026-10-09', 'end' => '2026-10-11', 'total' => '64.26 TND', 'status' => 'Confirmed', 'image' => 'images/front/battery-station.svg'],
        ['reference' => 'RES-1038', 'equipment' => 'Foldable solar panel 200 W', 'dates' => '16–17 Oct 2026', 'start' => '2026-10-16', 'end' => '2026-10-17', 'total' => '21.42 TND', 'status' => 'Pending', 'image' => 'images/front/solar-panel.svg'],
        ['reference' => 'RES-1021', 'equipment' => 'Portable wind turbine 400 W', 'dates' => '1–2 Sep 2026', 'start' => '2026-09-01', 'end' => '2026-09-02', 'total' => '71.40 TND', 'status' => 'Cancelled', 'image' => 'images/front/wind-turbine.svg'],
    ];
    $rentals = [
        ['reference' => 'RNT-2031', 'equipment' => 'Portable battery 1000 Wh', 'dates' => '2–4 Oct 2026', 'total' => '64.26 TND', 'status' => 'Active', 'image' => 'images/front/battery-station.svg'],
        ['reference' => 'RNT-2024', 'equipment' => 'Foldable solar panel 200 W', 'dates' => '12–14 Sep 2026', 'total' => '32.13 TND', 'status' => 'Completed', 'image' => 'images/front/solar-panel.svg'],
        ['reference' => 'RNT-2009', 'equipment' => 'Portable wind turbine 400 W', 'dates' => '8–9 Aug 2026', 'total' => '47.60 TND', 'status' => 'Completed', 'image' => 'images/front/wind-turbine.svg'],
    ];
    $statusStyles = [
        'Confirmed' => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-300',
        'Active' => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-300',
        'Paid' => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-300',
        'Approved' => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-300',
        'Pending' => 'bg-warning-50 text-warning-800 dark:bg-warning-500/15 dark:text-warning-300',
        'Requested' => 'bg-warning-50 text-warning-800 dark:bg-warning-500/15 dark:text-warning-300',
        'Completed' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        'Cancelled' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
        'Rejected' => 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-300',
    ];
    $spendingChart = [
        'chart' => ['type' => 'bar', 'toolbar' => ['show' => false]],
        'series' => [['name' => __('Spend (TND)'), 'data' => [42, 86, 54, 112, 76, 64, 98, 38, 64, 21, 0, 0]]],
        'xaxis' => ['categories' => [__('Nov'), __('Dec'), __('Jan'), __('Feb'), __('Mar'), __('Apr'), __('May'), __('Jun'), __('Jul'), __('Aug'), __('Sep'), __('Oct')]],
        'colors' => ['#176b45'],
        'plotOptions' => ['bar' => ['borderRadius' => 4, 'columnWidth' => '48%']],
        'dataLabels' => ['enabled' => false],
    ];
    $categoryChart = [
        'chart' => ['type' => 'donut', 'toolbar' => ['show' => false]],
        'series' => [4, 3, 2],
        'labels' => [__('Batteries'), __('Solar panels'), __('Wind equipment')],
        'colors' => ['#176b45', '#f4bd45', '#0ba5ec'],
        'legend' => ['position' => 'bottom'],
    ];
    $reservationChart = [
        'chart' => ['type' => 'donut', 'toolbar' => ['show' => false]],
        'series' => [2, 1, 1],
        'labels' => [__('Pending'), __('Confirmed'), __('Cancelled')],
        'colors' => ['#f4bd45', '#176b45', '#98a2b3'],
        'legend' => ['position' => 'bottom'],
    ];
    $rentalDaysChart = [
        'chart' => ['type' => 'line', 'toolbar' => ['show' => false]],
        'series' => [['name' => __('Rental days'), 'data' => [0, 2, 0, 0, 3, 0, 2, 3, 0, 3, 0, 0]]],
        'xaxis' => ['categories' => [__('Nov'), __('Dec'), __('Jan'), __('Feb'), __('Mar'), __('Apr'), __('May'), __('Jun'), __('Jul'), __('Aug'), __('Sep'), __('Oct')]],
        'colors' => ['#176b45'],
        'stroke' => ['curve' => 'smooth', 'width' => 3],
        'dataLabels' => ['enabled' => false],
    ];
    $extensionChart = [
        'chart' => ['type' => 'bar', 'stacked' => true, 'toolbar' => ['show' => false]],
        'series' => [
            ['name' => __('Requested'), 'data' => [0, 1, 1, 1]],
            ['name' => __('Approved'), 'data' => [0, 0, 1, 0]],
            ['name' => __('Rejected'), 'data' => [0, 0, 0, 1]],
        ],
        'xaxis' => ['categories' => [__('Jul'), __('Aug'), __('Sep'), __('Oct')]],
        'colors' => ['#f4bd45', '#176b45', '#f04438'],
        'plotOptions' => ['bar' => ['borderRadius' => 4, 'columnWidth' => '45%']],
        'dataLabels' => ['enabled' => false],
    ];
    $paymentMethodChart = [
        'chart' => ['type' => 'donut', 'toolbar' => ['show' => false]],
        'series' => [4, 2, 1],
        'labels' => [__('Card'), __('Bank transfer'), __('Cash')],
        'colors' => ['#176b45', '#f4bd45', '#0ba5ec'],
        'legend' => ['position' => 'bottom'],
    ];
@endphp

@section('content')
    <section class="relative isolate overflow-hidden bg-gray-950 text-white">
        <div class="pointer-events-none absolute inset-0 -z-20 bg-linear-to-br from-gray-950 via-brand-950 to-gray-900" aria-hidden="true"></div>
        <div class="hero-energy-grid pointer-events-none absolute inset-0 -z-10 opacity-20" aria-hidden="true"></div>
        <div class="mx-auto flex max-w-7xl flex-wrap items-end justify-between gap-5 px-4 py-9 sm:px-8 sm:py-12 lg:px-10">
            <div>
                <p class="text-theme-xs font-semibold uppercase tracking-[0.2em] text-warning-300">{{ __('My SolarShare') }} <span class="mx-1 text-gray-500">/</span> {{ __('Renter space') }}</p>
                <h1 class="mt-3 text-title-sm font-semibold tracking-tight sm:text-title-md">{{ __($title) }}</h1>
                <p class="mt-2 max-w-2xl text-theme-sm text-gray-300">{{ __('Your bookings, rentals and clean-energy journeys, all in one place.') }}</p>
            </div>
            <a href="{{ route('front.catalog') }}" class="button-base button-primary min-h-11 px-4 text-theme-sm">{{ __('Explore equipment') }} <span aria-hidden="true">→</span></a>
        </div>
    </section>

    @include('pages.front.partials.buyer-nav')

    <main class="mx-auto w-full max-w-7xl space-y-7 px-4 py-7 sm:px-8 sm:py-10 lg:px-10">
        <nav aria-label="{{ __('Breadcrumb') }}" class="flex items-center gap-2 text-theme-xs text-gray-500">
            <a href="{{ route('front.my-dashboard') }}" class="rounded-sm hover:text-brand-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:hover:text-brand-300">{{ __('My account') }}</a>
            <span aria-hidden="true">/</span><span aria-current="page" class="font-medium text-gray-800 dark:text-gray-200">{{ __($title) }}</span>
        </nav>
        <div class="flex items-start gap-3 rounded-2xl border border-warning-200 bg-warning-50 px-4 py-3.5 text-theme-xs leading-5 text-warning-900 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-200 print:hidden" role="status">
            <span class="mt-0.5 text-base" aria-hidden="true">✳</span>
            <p><span class="font-semibold">{{ __('Frontend preview with sample data.') }}</span> {{ __('Bookings, payments, invoices and actions shown here are illustrative; nothing is saved, charged or sent.') }}</p>
        </div>

        @if ($slug === 'my-reservations')
            <section class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]" x-data="{ status: 'all', cancelRef: '', message: '' }">
                <div class="flex flex-wrap items-end justify-between gap-4 border-b border-gray-100 p-5 dark:border-gray-800 sm:px-6">
                    <div><h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Your reservations') }}</h2><p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Review dates, booking status and payment progress.') }}</p></div>
                    <div class="flex flex-wrap items-center gap-3">
                        <label class="text-theme-xs text-gray-500">{{ __('Status') }} <select x-model="status" class="ms-2 min-h-10 rounded-lg border border-gray-300 bg-white px-3 text-theme-xs text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white"><option value="all">{{ __('All') }}</option><option value="Pending">{{ __('Pending') }}</option><option value="Confirmed">{{ __('Confirmed') }}</option><option value="Cancelled">{{ __('Cancelled') }}</option></select></label>
                        <a href="{{ route('front.catalog') }}" class="button-base button-primary min-h-10 px-4 text-theme-xs">{{ __('New reservation') }}</a>
                    </div>
                </div>
                <div class="overflow-x-auto"><table class="w-full min-w-[760px] text-start text-theme-xs"><thead class="bg-gray-50 text-gray-500 dark:bg-white/5 dark:text-gray-400"><tr>@foreach ([__('Reference'), __('Equipment'), __('Dates'), __('Total'), __('Status'), __('')] as $label)<th class="px-4 py-3 text-start font-medium">{{ $label }}</th>@endforeach</tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($reservations as $booking)
                        <tr x-show="status === 'all' || status === '{{ $booking['status'] }}'" class="text-gray-700 dark:text-gray-300">
                            <td class="px-4 py-4 font-semibold text-brand-700 dark:text-brand-300"><a href="{{ route('front.buyer-reservation-detail') }}">{{ $booking['reference'] }}</a></td>
                            <td class="px-4 py-4"><span class="flex items-center gap-3"><img src="{{ asset($booking['image']) }}" alt="" class="size-10 rounded-lg object-cover"><span class="font-medium text-gray-900 dark:text-white">{{ $booking['equipment'] }}</span></span></td>
                            <td class="px-4 py-4">{{ $booking['dates'] }}</td><td class="px-4 py-4 font-semibold text-gray-900 dark:text-white">{{ $booking['total'] }}</td>
                            <td class="px-4 py-4"><span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusStyles[$booking['status']] }}">{{ __($booking['status']) }}</span></td>
                            <td class="px-4 py-4"><a href="{{ route('front.buyer-reservation-detail') }}" class="font-semibold text-brand-700 dark:text-brand-300">{{ __('View') }}</a>@if ($booking['status'] === 'Pending')<button type="button" @click="cancelRef = '{{ $booking['reference'] }}'" class="ms-3 font-semibold text-error-700 dark:text-error-300">{{ __('Cancel') }}</button>@endif</td>
                        </tr>
                    @endforeach
                </tbody></table></div>
                <div class="flex items-center justify-between border-t border-gray-100 px-5 py-4 text-theme-xs text-gray-500 dark:border-gray-800"><span>{{ __('Showing 3 sample reservations') }}</span><div class="flex gap-2"><button disabled class="min-h-9 rounded-lg border border-gray-200 px-3 opacity-50 dark:border-gray-700">{{ __('Previous') }}</button><button aria-current="page" class="min-h-9 rounded-lg bg-brand-700 px-3 font-semibold text-white">1</button><button disabled class="min-h-9 rounded-lg border border-gray-200 px-3 opacity-50 dark:border-gray-700">{{ __('Next') }}</button></div></div>
                <div x-show="cancelRef" x-cloak @keydown.escape.window="cancelRef = ''" class="fixed inset-0 z-999999 flex items-center justify-center bg-gray-950/60 p-4">
                    <section role="alertdialog" aria-modal="true" aria-labelledby="cancel-reservation-title" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900">
                        <h2 id="cancel-reservation-title" class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Cancel this pending reservation?') }}</h2>
                        <p class="mt-2 text-theme-sm text-gray-600 dark:text-gray-300"><span x-text="cancelRef" class="font-semibold"></span> · {{ __('Only pending reservations can be cancelled. This is a preview; no booking will change.') }}</p>
                        <div class="mt-6 flex justify-end gap-3"><button type="button" @click="cancelRef = ''" class="min-h-10 rounded-lg border border-gray-300 px-4 text-theme-xs font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">{{ __('Keep reservation') }}</button><button type="button" @click="message = '{{ __('Preview only: the reservation was not cancelled.') }}'; cancelRef = ''" class="min-h-10 rounded-lg bg-error-600 px-4 text-theme-xs font-semibold text-white">{{ __('Confirm cancellation') }}</button></div>
                    </section>
                </div>
                <p x-show="message" x-cloak role="status" class="m-4 rounded-xl bg-warning-50 px-4 py-3 text-theme-xs text-warning-900 dark:bg-warning-500/10 dark:text-warning-200" x-text="message"></p>
            </section>
        @elseif ($slug === 'buyer-reservation-detail')
            <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-7">
                    <div class="flex flex-wrap items-start justify-between gap-3"><div><p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Reservation RES-1042') }}</p><h2 class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">{{ __('Portable battery 1000 Wh') }}</h2></div><span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusStyles['Confirmed'] }}">{{ __('Confirmed') }}</span></div>
                    <div class="mt-6 grid gap-3 sm:grid-cols-2">@foreach ([[__('Owner'), 'Sami Ben Salem'], [__('Period'), '9–11 Oct 2026 · 3 days'], [__('Price per day'), '18.00 TND'], [__('Pickup location'), 'La Marsa, Tunis']] as [$label, $value])<div class="rounded-xl bg-gray-50 p-4 dark:bg-white/5"><p class="text-theme-xs text-gray-500">{{ $label }}</p><p class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $value }}</p></div>@endforeach</div>
                    <div class="mt-6 border-t border-gray-100 pt-5 dark:border-gray-800"><h3 class="font-semibold text-gray-900 dark:text-white">{{ __('Price summary') }}</h3><dl class="mt-4 space-y-3 text-theme-sm"><div class="flex justify-between"><dt class="text-gray-500">{{ __('3 days × 18 TND') }}</dt><dd class="text-gray-800 dark:text-gray-200">54.00 TND</dd></div><div class="flex justify-between"><dt class="text-gray-500">{{ __('Tax') }}</dt><dd class="text-gray-800 dark:text-gray-200">10.26 TND</dd></div><div class="flex justify-between border-t border-gray-100 pt-3 dark:border-gray-800"><dt class="font-semibold text-gray-900 dark:text-white">{{ __('Total') }}</dt><dd class="font-semibold text-gray-900 dark:text-white">64.26 TND</dd></div></dl></div>
                    <div class="mt-6 flex flex-wrap items-center gap-3"><a href="{{ route('front.my-payments') }}" class="button-base button-primary min-h-10 px-4 text-theme-xs">{{ __('View payment & invoice') }}</a><span class="text-theme-xs text-gray-500">{{ __('Cancellation is available only while a reservation is pending.') }}</span></div>
                </article>
                <aside class="space-y-5">
                    <section class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"><h3 class="font-semibold text-gray-900 dark:text-white">{{ __('Booking journey') }}</h3><ol class="mt-5 space-y-4">@foreach ([[__('Reservation'), __('Confirmed'), true], [__('Payment'), __('Paid · Card'), true], [__('Invoice'), 'INV-2026-0142', true], [__('Rental'), __('Ready after owner hand-off'), false]] as [$label, $detail, $complete])<li class="flex gap-3"><span class="flex size-7 shrink-0 items-center justify-center rounded-full {{ $complete ? 'bg-success-100 text-success-700 dark:bg-success-500/15 dark:text-success-300' : 'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-gray-400' }}">{{ $complete ? '✓' : '4' }}</span><span class="text-theme-xs font-semibold text-gray-900 dark:text-white">{{ $label }}<small class="mt-1 block font-normal text-gray-500 dark:text-gray-400">{{ $detail }}</small></span></li>@endforeach</ol></section>
                    <section class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"><h3 class="font-semibold text-gray-900 dark:text-white">{{ __('Payment & invoice') }}</h3><dl class="mt-4 space-y-3 text-theme-xs"><div class="flex justify-between"><dt class="text-gray-500">{{ __('Method') }}</dt><dd class="font-medium text-gray-900 dark:text-white">{{ __('Card') }}</dd></div><div class="flex justify-between"><dt class="text-gray-500">{{ __('Payment status') }}</dt><dd class="font-medium text-success-700 dark:text-success-300">{{ __('Paid') }}</dd></div><div class="flex justify-between"><dt class="text-gray-500">{{ __('Invoice') }}</dt><dd class="font-medium text-gray-900 dark:text-white">INV-2026-0142</dd></div></dl><button type="button" onclick="window.print()" class="mt-5 min-h-10 w-full rounded-lg border border-gray-300 text-theme-xs font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">{{ __('Print invoice') }}</button></section>
                </aside>
            </section>
        @elseif ($slug === 'my-rentals')
            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex flex-wrap items-end justify-between gap-3 border-b border-gray-100 p-5 dark:border-gray-800 sm:px-6"><div><h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Your rental history') }}</h2><p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Active trips and completed equipment hand-offs.') }}</p></div><span class="rounded-full bg-brand-50 px-3 py-1 text-theme-xs font-semibold text-brand-800 dark:bg-brand-500/15 dark:text-brand-300">3 {{ __('sample rentals') }}</span></div>
                <div class="overflow-x-auto"><table class="w-full min-w-[760px] text-start text-theme-xs"><thead class="bg-gray-50 text-gray-500 dark:bg-white/5 dark:text-gray-400"><tr>@foreach ([__('Reference'), __('Equipment'), __('Dates'), __('Amount'), __('Status'), __('')] as $label)<th class="px-4 py-3 text-start font-medium">{{ $label }}</th>@endforeach</tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-800">@foreach ($rentals as $rental)<tr><td class="px-4 py-4 font-semibold text-brand-700 dark:text-brand-300">{{ $rental['reference'] }}</td><td class="px-4 py-4 text-gray-900 dark:text-white">{{ $rental['equipment'] }}</td><td class="px-4 py-4 text-gray-600 dark:text-gray-300">{{ $rental['dates'] }}</td><td class="px-4 py-4 font-medium text-gray-900 dark:text-white">{{ $rental['total'] }}</td><td class="px-4 py-4"><span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusStyles[$rental['status']] }}">{{ __($rental['status']) }}</span></td><td class="px-4 py-4"><a href="{{ route('front.buyer-rental-detail') }}" class="font-semibold text-brand-700 dark:text-brand-300">{{ __('Details') }}</a></td></tr>@endforeach</tbody></table></div>
            </section>
            <div class="grid gap-5 xl:grid-cols-2"><x-common.component-card :title="__('Rental days per month')" :desc="__('Illustrative days used across the last 12 months.')"><x-charts.apex :options="$rentalDaysChart" :height="270" :label="__('Rental days per month')" /></x-common.component-card><x-common.component-card :title="__('Extensions')" :desc="__('Example extension requests by status and month.')"><x-charts.apex :options="$extensionChart" :height="270" :label="__('Extensions by status')" /></x-common.component-card></div>
        @elseif ($slug === 'buyer-rental-detail')
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-7" x-data="{ tab: 'contract' }">
                <div class="flex flex-wrap items-start justify-between gap-4"><div><p class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Rental RNT-2031') }}</p><h2 class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">{{ __('Portable battery 1000 Wh') }}</h2><p class="mt-1 text-theme-xs text-gray-500">{{ __('Owner: Sami Ben Salem · 2–4 Oct 2026') }}</p></div><span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusStyles['Active'] }}">{{ __('Active') }}</span></div>
                <div class="mt-6 grid gap-3 sm:grid-cols-3">@foreach ([[__('Rental amount'), '64.26 TND'], [__('Deposit · information only'), '100.00 TND'], [__('Return date'), '4 Oct 2026']] as [$label, $value])<div class="rounded-xl bg-gray-50 p-4 dark:bg-white/5"><p class="text-theme-xs text-gray-500">{{ $label }}</p><p class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $value }}</p></div>@endforeach</div>
                <div class="mt-6 flex flex-wrap gap-2 border-b border-gray-200 dark:border-gray-800">@foreach (['contract' => __('Contract'), 'extensions' => __('Extension history'), 'inspection' => __('Inspection')] as $key => $label)<button type="button" @click="tab = '{{ $key }}'" :aria-pressed="tab === '{{ $key }}'" class="min-h-11 px-4 text-theme-xs font-semibold" :class="tab === '{{ $key }}' ? 'border-b-2 border-brand-600 text-brand-700 dark:text-brand-300' : 'text-gray-500'">{{ $label }}</button>@endforeach</div>
                <div x-show="tab === 'contract'" class="py-6"><div class="flex flex-wrap items-center justify-between gap-3"><div><h3 class="font-semibold text-gray-900 dark:text-white">{{ __('Contract CTR-2031-01') }}</h3><p class="mt-1 text-theme-xs text-gray-500">{{ __('Signed') }} 1 Oct 2026 · {{ __('Deposit') }} 100 TND</p></div><button type="button" onclick="window.print()" class="min-h-10 rounded-lg border border-gray-300 px-4 text-theme-xs font-semibold dark:border-gray-700">{{ __('Print contract') }}</button></div><p class="mt-5 max-w-3xl text-theme-sm leading-7 text-gray-600 dark:text-gray-300">{{ __('Use the equipment according to its instructions, keep it secure and return it in the recorded condition. Report any damage promptly. Deposit is shown for information only in this preview.') }}</p></div>
                <div x-show="tab === 'extensions'" x-cloak class="py-6"><p class="text-theme-sm text-gray-700 dark:text-gray-300">{{ __('No extension has been approved for this rental yet.') }}</p><a href="{{ route('front.my-extensions') }}" class="mt-4 inline-flex min-h-10 items-center rounded-lg bg-brand-50 px-4 text-theme-xs font-semibold text-brand-800 dark:bg-brand-500/10 dark:text-brand-200">{{ __('Request an extension') }}</a></div>
                <div x-show="tab === 'inspection'" x-cloak class="py-6"><div class="grid gap-4 sm:grid-cols-2"><div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800"><p class="text-theme-xs text-gray-500">{{ __('Pickup condition') }}</p><p class="mt-2 font-semibold text-success-700 dark:text-success-300">{{ __('Excellent · 2 Oct 2026') }}</p></div><div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800"><p class="text-theme-xs text-gray-500">{{ __('Return inspection') }}</p><p class="mt-2 font-semibold text-gray-700 dark:text-gray-200">{{ __('Due after return') }}</p></div></div></div>
            </section>
        @elseif ($slug === 'my-contract')
            <section class="mx-auto max-w-3xl rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-9"><div class="flex flex-wrap items-start justify-between gap-4 border-b border-gray-100 pb-5 dark:border-gray-800"><div><p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-700 dark:text-brand-300">{{ __('Rental contract') }}</p><h2 class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">CTR-2031-01</h2><p class="mt-1 text-theme-xs text-gray-500">{{ __('RNT-2031 · Signed 1 Oct 2026') }}</p></div><button type="button" onclick="window.print()" class="min-h-10 rounded-lg border border-gray-300 px-4 text-theme-xs font-semibold dark:border-gray-700">{{ __('Print contract') }}</button></div><dl class="grid gap-4 py-5 sm:grid-cols-2">@foreach ([[__('Equipment'), 'Portable battery 1000 Wh'], [__('Rental period'), '2–4 Oct 2026'], [__('Deposit · information only'), '100.00 TND'], [__('Status'), __('Signed')]] as [$label, $value])<div><dt class="text-theme-xs text-gray-500">{{ $label }}</dt><dd class="mt-1 text-theme-sm font-semibold text-gray-900 dark:text-white">{{ $value }}</dd></div>@endforeach</dl><div class="border-t border-gray-100 pt-5 text-theme-sm leading-7 text-gray-600 dark:border-gray-800 dark:text-gray-300"><h3 class="font-semibold text-gray-900 dark:text-white">{{ __('Terms of use') }}</h3><p class="mt-3">{{ __('The renter agrees to follow the manufacturer instructions, use the equipment safely, keep it secure and return it on the agreed date in the recorded condition. Any incident should be reported to the owner promptly.') }}</p><p class="mt-3">{{ __('The deposit shown is informational in this frontend preview. No payment or deposit is being collected.') }}</p></div></section>
        @elseif ($slug === 'my-extensions')
            <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                <form @submit.prevent="message = '{{ __('Preview only: your extension request was not sent or saved.') }}'" x-data="{ days: 1, message: '' }" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Request more time') }}</h2><p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Ask the owner to extend an active rental.') }}</p>
                    <div class="mt-5 space-y-4"><label class="block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Rental') }}<select class="mt-1.5 min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"><option>RNT-2031 · Portable battery 1000 Wh</option></select></label><div class="grid gap-3 sm:grid-cols-2"><div class="rounded-xl bg-gray-50 p-3 dark:bg-white/5"><p class="text-[11px] text-gray-500">{{ __('Current return date') }}</p><p class="mt-1 text-theme-sm font-semibold text-gray-900 dark:text-white">4 Oct 2026</p></div><label class="block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('New end date') }}<input type="date" min="2026-10-05" value="2026-10-05" x-on:change="days = Math.max(1, Math.round((new Date($event.target.value) - new Date('2026-10-04')) / 86400000))" class="mt-1.5 min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"></label></div><div class="rounded-xl bg-brand-50 p-4 dark:bg-brand-500/10"><p class="text-theme-xs text-brand-800 dark:text-brand-200">{{ __('Illustrative additional amount') }}</p><p class="mt-1 text-xl font-semibold text-brand-900 dark:text-white"><span x-text="days * 18"></span> TND</p><p class="mt-1 text-[11px] text-brand-700 dark:text-brand-300">{{ __('Calculated at 18 TND per extra day; owner approval required.') }}</p></div><label class="block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Reason') }}<textarea required rows="3" placeholder="{{ __('Tell the owner why you need more time…') }}" class="mt-1.5 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea></label><button type="submit" class="button-base button-primary min-h-11 w-full px-4 text-theme-sm">{{ __('Send extension request') }}</button><p x-show="message" x-cloak role="status" class="rounded-lg bg-warning-50 p-3 text-theme-xs text-warning-900 dark:bg-warning-500/10 dark:text-warning-200" x-text="message"></p></div>
                </form>
                <section class="space-y-5"><div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"><div class="border-b border-gray-100 p-5 dark:border-gray-800"><h2 class="font-semibold text-gray-900 dark:text-white">{{ __('Extension history') }}</h2><p class="mt-1 text-theme-xs text-gray-500">{{ __('Sample requests and owner decisions.') }}</p></div><div class="overflow-x-auto"><table class="w-full min-w-[580px] text-start text-theme-xs"><thead class="bg-gray-50 text-gray-500 dark:bg-white/5"><tr>@foreach ([__('Rental'), __('Requested end'), __('Extra'), __('Status')] as $label)<th class="px-4 py-3 text-start font-medium">{{ $label }}</th>@endforeach</tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-800">@foreach ([['RNT-2031', '5 Oct 2026', '18 TND', 'Requested'], ['RNT-2024', '15 Sep 2026', '9 TND', 'Approved'], ['RNT-2009', '10 Aug 2026', '12 TND', 'Rejected']] as [$ref, $end, $price, $state])<tr><td class="px-4 py-4 font-semibold text-gray-900 dark:text-white">{{ $ref }}</td><td class="px-4 py-4 text-gray-600 dark:text-gray-300">{{ $end }}</td><td class="px-4 py-4 text-gray-600 dark:text-gray-300">{{ $price }}</td><td class="px-4 py-4"><span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusStyles[$state] }}">{{ __($state) }}</span></td></tr>@endforeach</tbody></table></div></div><x-common.component-card :title="__('Extensions by status')" :desc="__('Illustrative requests over the last four months.')"><x-charts.apex :options="$extensionChart" :height="250" :label="__('Extensions by status')" /></x-common.component-card></section>
            </div>
        @elseif ($slug === 'my-payments')
            <section class="grid gap-4 sm:grid-cols-3">@foreach ([[__('Total paid'), '163.99 TND', 'success'], [__('Successful payments'), '3', 'brand'], [__('Pending'), '21.42 TND', 'warning']] as [$label, $value, $tone])<article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]"><p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ $label }}</p><p class="mt-3 text-2xl font-semibold text-gray-900 dark:text-white">{{ $value }}</p><p class="mt-2 text-[11px] text-gray-500">{{ __('Illustrative account summary') }}</p></article>@endforeach</section>
            <div class="grid gap-5 xl:grid-cols-2"><x-common.component-card :title="__('Spending per month')" :desc="__('Example paid totals for the past 12 months.')"><x-charts.apex :options="$spendingChart" :height="260" :label="__('Spending per month')" /></x-common.component-card><x-common.component-card :title="__('Payments by method')" :desc="__('Illustrative payment method mix.')"><x-charts.apex :options="$paymentMethodChart" :height="260" :label="__('Payments by method')" /></x-common.component-card></div>
            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"><div class="flex flex-wrap items-end justify-between gap-3 border-b border-gray-100 p-5 dark:border-gray-800"><div><h2 class="font-semibold text-gray-900 dark:text-white">{{ __('Payments & invoices') }}</h2><p class="mt-1 text-theme-xs text-gray-500">{{ __('Filter by period and print a sample invoice.') }}</p></div><div class="flex gap-2"><label class="text-theme-xs text-gray-500">{{ __('Date from') }}<input type="date" class="ms-2 min-h-9 rounded-lg border border-gray-300 bg-white px-2 text-theme-xs dark:border-gray-700 dark:bg-gray-900"></label><label class="text-theme-xs text-gray-500">{{ __('To') }}<input type="date" class="ms-2 min-h-9 rounded-lg border border-gray-300 bg-white px-2 text-theme-xs dark:border-gray-700 dark:bg-gray-900"></label></div></div><div class="overflow-x-auto"><table class="w-full min-w-[680px] text-start text-theme-xs"><thead class="bg-gray-50 text-gray-500 dark:bg-white/5"><tr>@foreach ([__('Date'), __('Reference'), __('Payment method'), __('Invoice'), __('Amount'), __('Status'), __('')] as $label)<th class="px-4 py-3 text-start font-medium">{{ $label }}</th>@endforeach</tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-800">@foreach ([['3 Oct 2026', 'RES-1042', 'CARD', 'INV-2026-0142', '64.26 TND', 'Paid'], ['16 Oct 2026', 'RES-1038', 'BANK_TRANSFER', 'INV-2026-0148', '21.42 TND', 'Pending'], ['12 Sep 2026', 'RES-1014', 'CASH', 'INV-2026-0131', '32.13 TND', 'Paid']] as [$date, $ref, $method, $invoice, $amount, $state])<tr><td class="px-4 py-4 text-gray-600 dark:text-gray-300">{{ $date }}</td><td class="px-4 py-4 font-semibold text-gray-900 dark:text-white">{{ $ref }}</td><td class="px-4 py-4 text-gray-600 dark:text-gray-300">{{ $method }}</td><td class="px-4 py-4 text-gray-700 dark:text-gray-200">{{ $invoice }}</td><td class="px-4 py-4 font-semibold text-gray-900 dark:text-white">{{ $amount }}</td><td class="px-4 py-4"><span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusStyles[$state] }}">{{ __($state) }}</span></td><td class="px-4 py-4"><button type="button" onclick="window.print()" class="font-semibold text-brand-700 dark:text-brand-300">{{ __('Print') }}</button></td></tr>@endforeach</tbody></table></div></section>
        @elseif ($slug === 'my-notifications')
            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"><div class="border-b border-gray-100 p-5 dark:border-gray-800"><h2 class="font-semibold text-gray-900 dark:text-white">{{ __('Your updates') }}</h2><p class="mt-1 text-theme-xs text-gray-500">{{ __('Sample notifications from your rental journey.') }}</p></div><ol class="divide-y divide-gray-100 dark:divide-gray-800">@foreach ([[__('Reservation confirmed'), __('RES-1042 is confirmed for 9–11 Oct.'), __('Today · 10:42'), 'front.my-reservations'], [__('Payment received'), __('Payment completed and invoice INV-2026-0142 is ready.'), __('Yesterday · 16:08'), 'front.my-payments'], [__('Extension update'), __('The owner approved your request for RNT-2024.'), __('28 Sep · 11:30'), 'front.my-extensions'], [__('Rental ending soon'), __('RNT-2031 is due back tomorrow. Remember the hand-off.'), __('27 Sep · 09:15'), 'front.my-rentals']] as [$heading, $detail, $time, $route])<li class="flex flex-wrap items-start justify-between gap-3 px-5 py-5 sm:px-6"><div class="flex gap-3"><span class="mt-1.5 size-2.5 shrink-0 rounded-full bg-brand-500"></span><div><h3 class="text-theme-sm font-semibold text-gray-900 dark:text-white">{{ $heading }}</h3><p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ $detail }}</p><time class="mt-2 block text-[11px] text-gray-400">{{ $time }}</time></div></div><a href="{{ route($route) }}" class="min-h-9 rounded-lg px-3 py-2 text-theme-xs font-semibold text-brand-700 hover:bg-brand-50 dark:text-brand-300 dark:hover:bg-brand-500/10">{{ __('Open') }}</a></li>@endforeach</ol></section>
        @endif
    </main>
@endsection
