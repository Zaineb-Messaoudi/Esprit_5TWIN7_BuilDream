@extends('layouts.front')

@section('content')
<div class="relative isolate overflow-hidden bg-gray-950 text-white">
    <div class="pointer-events-none absolute inset-0 -z-10 bg-linear-to-br from-gray-950 via-brand-950 to-gray-900" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -end-20 -top-24 -z-10 size-80 rounded-full bg-warning-400/15 blur-3xl" aria-hidden="true"></div>
    <div class="mx-auto max-w-7xl px-6 py-12 sm:px-8 sm:py-16 lg:px-10">
        <p class="text-theme-xs font-semibold uppercase tracking-[0.2em] text-warning-300">{{ __('SolarShare / energy studio') }}</p>
        <h1 class="mt-4 max-w-3xl text-title-lg font-semibold tracking-tight sm:text-title-xl">{{ __('Find the right amount of power before you book.') }}</h1>
        <p class="mt-4 max-w-2xl text-lg leading-7 text-gray-300">{{ __('Estimate your daily energy need, then match it with real catalogue specifications.') }}</p>
        <div class="mt-7 flex flex-wrap gap-3 text-theme-xs font-medium text-gray-200"><span class="rounded-full border border-white/15 bg-white/10 px-3 py-1.5">{{ __('Uses EnergyProfile data') }}</span><span class="rounded-full border border-white/15 bg-white/10 px-3 py-1.5">{{ __('No guesswork') }}</span></div>
    </div>
</div>

<main class="mx-auto grid w-full max-w-7xl gap-8 px-6 py-8 sm:px-8 sm:py-12 lg:grid-cols-[minmax(0,1fr)_22rem] lg:px-10">
    <section class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03] sm:p-7">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-gray-100 pb-5 dark:border-gray-800">
            <div><p class="text-theme-xs font-semibold uppercase tracking-[0.16em] text-brand-700 dark:text-brand-300">{{ __('Step 1') }}</p><h2 class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ __('What will you power?') }}</h2><p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ __('Set a quantity and daily usage for each appliance.') }}</p></div>
            <a href="{{ route('front.equipment.compare') }}" class="text-theme-sm font-semibold text-brand-700 hover:underline dark:text-brand-300">{{ __('Compare equipment') }} →</a>
        </div>
        <form method="GET" action="{{ route('front.energy-calculator') }}" class="mt-6 space-y-4">
            <input type="hidden" name="calculate" value="1">
            @foreach($appliances as $appliance)
                <div class="grid gap-4 rounded-2xl border border-gray-200 bg-gray-50/70 p-4 sm:grid-cols-[1fr_9rem_9rem] sm:items-center dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-center gap-3"><span class="grid size-11 place-items-center rounded-xl bg-warning-100 text-xl dark:bg-warning-500/15">{{ $appliance['icon'] }}</span><div><p class="font-semibold text-gray-900 dark:text-white">{{ __($appliance['label']) }}</p><p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ $appliance['watts'] }} W · {{ __('typical draw') }}</p></div></div>
                    <label class="text-theme-xs font-medium text-gray-600 dark:text-gray-300">{{ __('Quantity') }}<input name="{{ $appliance['key'] }}_quantity" type="number" min="0" max="10" value="{{ $appliance['quantity'] }}" class="mt-1.5 min-h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></label>
                    <label class="text-theme-xs font-medium text-gray-600 dark:text-gray-300">{{ __('Hours / day') }}<input name="{{ $appliance['key'] }}_hours" type="number" min="0" max="24" step="0.5" value="{{ $appliance['selected_hours'] }}" class="mt-1.5 min-h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></label>
                </div>
            @endforeach
            <div class="flex flex-wrap items-center justify-between gap-3 pt-3"><p class="max-w-md text-theme-xs leading-5 text-gray-500 dark:text-gray-400">{{ __('The estimate is illustrative. Real usage depends on device cycles, weather and charging losses.') }}</p><button type="submit" class="button-base button-primary min-h-11 px-5 text-theme-sm">{{ __('Calculate my need') }} <span aria-hidden="true">↗</span></button></div>
        </form>
    </section>

    <aside class="space-y-5">
        <section class="rounded-3xl border border-brand-200 bg-brand-50 p-5 shadow-theme-sm dark:border-brand-500/30 dark:bg-brand-500/10 sm:p-6">
            <p class="text-theme-xs font-semibold uppercase tracking-[0.16em] text-brand-700 dark:text-brand-200">{{ __('Your estimate') }}</p>
            @if($calculated)
                <p class="mt-3 text-4xl font-semibold tracking-tight text-brand-950 dark:text-white">{{ number_format($requiredWh) }} <span class="text-base font-medium">Wh/day</span></p>
                <p class="mt-2 text-theme-sm text-brand-800 dark:text-brand-100">{{ __('Minimum capacity') }}: {{ number_format($requiredWh) }} Wh · {{ __('simultaneous power') }}: {{ number_format($requiredPower) }} W</p>
            @else
                <p class="mt-3 text-2xl font-semibold tracking-tight text-brand-950 dark:text-white">{{ __('Ready when you are') }}</p>
                <p class="mt-2 text-theme-sm leading-6 text-brand-800 dark:text-brand-100">{{ __('Enter your appliances to unlock equipment recommendations.') }}</p>
            @endif
        </section>
        <section class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03] sm:p-6"><p class="text-theme-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">{{ __('How it works') }}</p><ol class="mt-4 space-y-4 text-theme-sm text-gray-600 dark:text-gray-300"><li class="flex gap-3"><span class="grid size-7 shrink-0 place-items-center rounded-full bg-gray-900 text-xs font-semibold text-white dark:bg-white dark:text-gray-900">1</span><span>{{ __('We estimate watt-hours from quantity × watts × hours.') }}</span></li><li class="flex gap-3"><span class="grid size-7 shrink-0 place-items-center rounded-full bg-gray-900 text-xs font-semibold text-white dark:bg-white dark:text-gray-900">2</span><span>{{ __('We filter published equipment by capacity and output power.') }}</span></li><li class="flex gap-3"><span class="grid size-7 shrink-0 place-items-center rounded-full bg-gray-900 text-xs font-semibold text-white dark:bg-white dark:text-gray-900">3</span><span>{{ __('You choose what fits your real plan.') }}</span></li></ol></section>
    </aside>
</main>

@if($calculated)
<section class="mx-auto w-full max-w-7xl px-6 pb-12 sm:px-8 lg:px-10">
    <div class="mb-5"><p class="text-theme-xs font-semibold uppercase tracking-[0.16em] text-brand-700 dark:text-brand-300">{{ __('Step 2') }}</p><h2 class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ __('Equipment that can carry the load') }}</h2></div>
    @if($recommendations->isEmpty())
        <div class="rounded-3xl border border-warning-200 bg-warning-50 p-6 text-theme-sm text-warning-900 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-100">{{ __('No published listing matches both your capacity and power needs yet. Try lowering the quantity or browse the full catalogue.') }} <a class="font-semibold underline" href="{{ route('front.catalog') }}">{{ __('Open catalogue') }}</a></div>
    @else
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">@foreach($recommendations as $item)<article class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-theme-sm transition-transform hover:-translate-y-1 dark:border-gray-800 dark:bg-white/[0.03]"><div class="h-32 bg-gray-100 dark:bg-gray-900"><img src="{{ $item->image }}" alt="" class="size-full object-cover"></div><div class="p-5"><div class="flex items-start justify-between gap-3"><div><p class="text-theme-xs text-gray-500">{{ $item->brand }} {{ $item->model }}</p><h3 class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $item->name }}</h3></div><span class="rounded-full bg-success-50 px-2.5 py-1 text-[11px] font-semibold text-success-700 dark:bg-success-500/10 dark:text-success-300">{{ __('Fits') }}</span></div><div class="mt-5 grid grid-cols-2 gap-3 text-theme-xs"><div class="rounded-xl bg-gray-50 p-3 dark:bg-white/5"><span class="text-gray-500">{{ __('Capacity') }}</span><strong class="mt-1 block text-gray-900 dark:text-white">{{ $item->energyProfile?->capacity_wh ?? '—' }} Wh</strong></div><div class="rounded-xl bg-gray-50 p-3 dark:bg-white/5"><span class="text-gray-500">{{ __('Power') }}</span><strong class="mt-1 block text-gray-900 dark:text-white">{{ $item->energyProfile?->power_watts ?? $item->energyProfile?->max_output ?? '—' }} W</strong></div></div><a href="{{ route('front.equipment.show', $item->id) }}" class="mt-5 inline-flex font-semibold text-brand-700 hover:underline dark:text-brand-300">{{ __('View details') }} →</a></div></article>@endforeach</div>
    @endif
</section>
@endif
@endsection
