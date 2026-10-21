@extends('layouts.front')

@section('content')
<div class="relative isolate overflow-hidden bg-gray-950 text-white">
    <div class="pointer-events-none absolute inset-0 -z-10 bg-linear-to-br from-gray-950 via-brand-950 to-gray-900" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -end-20 -top-24 -z-10 size-80 rounded-full bg-warning-400/15 blur-3xl" aria-hidden="true"></div>
    <div class="mx-auto max-w-7xl px-6 py-12 sm:px-8 sm:py-16 lg:px-10">
        <p class="text-theme-xs font-semibold uppercase tracking-[0.2em] text-warning-300">{{ __('SolarShare / energy studio') }}</p>
        <h1 class="mt-4 max-w-3xl text-title-lg font-semibold tracking-tight sm:text-title-xl">{{ __('Energy calculator') }}</h1>
        <p class="mt-4 max-w-2xl text-lg leading-7 text-gray-300">{{ __('Estimate your daily energy need, then match it with real catalogue specifications.') }}</p>
        <div class="mt-7 flex flex-wrap gap-3 text-theme-xs font-medium text-gray-200">
            <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1.5">{{ __('Uses EnergyProfile data') }}</span>
            <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1.5">{{ __('No guesswork') }}</span>
        </div>
    </div>
</div>

<main class="mx-auto grid w-full max-w-7xl gap-8 px-6 py-8 sm:px-8 sm:py-12 lg:grid-cols-[minmax(0,1fr)_22rem] lg:px-10">
    <section class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03] sm:p-7">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-gray-100 pb-5 dark:border-gray-800">
            <div>
                <p class="text-theme-xs font-semibold uppercase tracking-[0.16em] text-brand-700 dark:text-brand-300">{{ __('Step 1') }}</p>
                <h2 class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ __('What will you power?') }}</h2>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ __('Set a quantity and daily usage for each appliance.') }}</p>
            </div>
            <a href="{{ route('front.equipment.compare') }}" class="text-theme-sm font-semibold text-brand-700 hover:underline dark:text-brand-300">{{ __('Compare equipment') }} →</a>
        </div>

        <form method="POST" action="{{ route('front.energy-calculator.calculate') }}" class="mt-6 space-y-4">
            @csrf
            @foreach($appliances as $appliance)
                <div class="grid gap-4 rounded-2xl border border-gray-200 bg-gray-50/70 p-4 sm:grid-cols-[1fr_9rem_9rem] sm:items-center dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-center gap-3">
                        <span class="grid size-11 place-items-center rounded-xl bg-warning-100 text-xl dark:bg-warning-500/15">{{ $appliance['icon'] }}</span>
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ __($appliance['label']) }}</p>
                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ $appliance['watts'] }} W · {{ __('typical draw') }}</p>
                        </div>
                    </div>
                    <label class="text-theme-xs font-medium text-gray-600 dark:text-gray-300">
                        {{ __('Quantity') }}
                        <input name="{{ $appliance['key'] }}_quantity" type="number" min="0" max="10" value="{{ old($appliance['key'].'_quantity', $appliance['quantity'] ?? 0) }}" class="mt-1.5 min-h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </label>
                    <label class="text-theme-xs font-medium text-gray-600 dark:text-gray-300">
                        {{ __('Hours / day') }}
                        <input name="{{ $appliance['key'] }}_hours" type="number" min="0" max="24" step="0.5" value="{{ old($appliance['key'].'_hours', $appliance['selected_hours'] ?? 0) }}" class="mt-1.5 min-h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </label>
                </div>
            @endforeach

            <div class="grid gap-4 rounded-2xl border border-gray-200 bg-gray-50/70 p-4 sm:grid-cols-2 dark:border-gray-800 dark:bg-white/[0.03]">
                <label class="text-theme-xs font-medium text-gray-600 dark:text-gray-300">
                    {{ __('Days of autonomy') }}
                    <input name="autonomy_days" type="number" min="0.5" max="5" step="0.5" value="{{ old('autonomy_days', 1) }}" class="mt-1.5 min-h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </label>
                <label class="text-theme-xs font-medium text-gray-600 dark:text-gray-300">
                    {{ __('Sun hours per day') }}
                    <input name="sun_hours" type="number" min="2" max="8" step="0.5" value="{{ old('sun_hours', 5) }}" class="mt-1.5 min-h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </label>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 pt-3">
                <p class="max-w-md text-theme-xs leading-5 text-gray-500 dark:text-gray-400">{{ __('The estimate is illustrative. Real usage depends on device cycles, weather and charging losses.') }}</p>
                <button type="submit" class="button-base button-primary min-h-11 px-5 text-theme-sm">{{ __('Calculate my need') }} <span aria-hidden="true">↗</span></button>
            </div>
        </form>
    </section>

    <aside class="space-y-5">
        <section class="rounded-3xl border border-brand-200 bg-brand-50 p-5 shadow-theme-sm dark:border-brand-500/30 dark:bg-brand-500/10 sm:p-6">
            <p class="text-theme-xs font-semibold uppercase tracking-[0.16em] text-brand-700 dark:text-brand-200">{{ __('Your estimate') }}</p>
            @if(isset($result) && $result['status'] === 'ok')
                <p class="mt-3 text-4xl font-semibold tracking-tight text-brand-950 dark:text-white">{{ number_format($result['daily_wh'], 0) }} <span class="text-base font-medium">Wh/day</span></p>
                <p class="mt-2 text-theme-sm text-brand-800 dark:text-brand-100">{{ __('Minimum capacity') }}: {{ number_format($result['daily_wh'], 0) }} Wh · {{ __('simultaneous power') }}: {{ number_format($result['required_power'] ?? $result['panel_w'], 0) }} W</p>
            @elseif(isset($result) && $result['status'] === 'empty')
                <p class="mt-3 text-2xl font-semibold tracking-tight text-brand-950 dark:text-white">{{ __('Select at least one appliance.') }}</p>
                <p class="mt-2 text-theme-sm leading-6 text-brand-800 dark:text-brand-100">{{ __('Choose an appliance and set quantity to calculate your energy needs.') }}</p>
            @else
                <p class="mt-3 text-2xl font-semibold tracking-tight text-brand-950 dark:text-white">{{ __('Ready when you are') }}</p>
                <p class="mt-2 text-theme-sm leading-6 text-brand-800 dark:text-brand-100">{{ __('Enter your appliances to unlock equipment recommendations.') }}</p>
            @endif
        </section>

        @if(isset($result) && $result['status'] === 'ok')
            <section class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03] sm:p-6" aria-labelledby="sizing-title">
                <p id="sizing-title" class="text-theme-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">{{ __('Suggested system size') }}</p>
                <dl class="mt-4 grid grid-cols-1 gap-3 text-theme-sm">
                    <div class="flex items-center justify-between rounded-xl bg-gray-50 p-3 dark:bg-white/5">
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Daily consumption') }}</dt>
                        <dd class="font-semibold text-gray-900 dark:text-white">{{ number_format($result['daily_wh'], 0, ',', ' ') }} Wh</dd>
                    </div>
                    <div class="flex items-center justify-between rounded-xl bg-gray-50 p-3 dark:bg-white/5">
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Solar panel power') }}</dt>
                        <dd class="font-semibold text-gray-900 dark:text-white">{{ number_format($result['panel_w'], 0, ',', ' ') }} W</dd>
                    </div>
                    <div class="flex items-center justify-between rounded-xl bg-gray-50 p-3 dark:bg-white/5">
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Battery capacity') }}</dt>
                        <dd class="font-semibold text-gray-900 dark:text-white">{{ number_format($result['battery_wh'], 0, ',', ' ') }} Wh</dd>
                    </div>
                    <div class="flex items-center justify-between rounded-xl bg-gray-50 p-3 dark:bg-white/5">
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Inverter power') }}</dt>
                        <dd class="font-semibold text-gray-900 dark:text-white">{{ number_format($result['inverter_w'], 0, ',', ' ') }} W</dd>
                    </div>
                </dl>

                <h3 class="mt-5 text-theme-xs font-semibold text-gray-900 dark:text-white">{{ __('Assumptions used') }}</h3>
                <ul class="mt-2 list-disc space-y-1 ps-5 text-theme-xs text-gray-600 dark:text-gray-300">
                    <li>{{ __('Days of autonomy') }} : {{ $result['assumptions']['autonomy_days'] }}</li>
                    <li>{{ __('Sun hours per day') }} : {{ $result['assumptions']['sun_hours'] }}</li>
                    <li>{{ __('Inverter efficiency') }} : {{ round($result['assumptions']['inverter_efficiency'] * 100) }} %</li>
                    <li>{{ __('Usable battery depth') }} : {{ round($result['assumptions']['battery_depth_of_discharge'] * 100) }} %</li>
                    <li>{{ __('Panel system efficiency') }} : {{ round($result['assumptions']['panel_system_efficiency'] * 100) }} %</li>
                    <li>{{ __('Inverter safety margin') }} : {{ round(($result['assumptions']['inverter_margin'] - 1) * 100) }} %</li>
                </ul>
                <p class="mt-3 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('This is an estimate based on typical appliance values. Check real power ratings before renting.') }}</p>
            </section>
        @endif

        <section class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
            <p class="text-theme-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">{{ __('How it works') }}</p>
            <ol class="mt-4 space-y-4 text-theme-sm text-gray-600 dark:text-gray-300">
                <li class="flex gap-3">
                    <span class="grid size-7 shrink-0 place-items-center rounded-full bg-gray-900 text-xs font-semibold text-white dark:bg-white dark:text-gray-900">1</span>
                    <span>{{ __('We estimate watt-hours from quantity × watts × hours.') }}</span>
                </li>
                <li class="flex gap-3">
                    <span class="grid size-7 shrink-0 place-items-center rounded-full bg-gray-900 text-xs font-semibold text-white dark:bg-white dark:text-gray-900">2</span>
                    <span>{{ __('We filter published equipment by capacity and output power.') }}</span>
                </li>
                <li class="flex gap-3">
                    <span class="grid size-7 shrink-0 place-items-center rounded-full bg-gray-900 text-xs font-semibold text-white dark:bg-white dark:text-gray-900">3</span>
                    <span>{{ __('You choose what fits your real plan.') }}</span>
                </li>
            </ol>
        </section>
    </aside>
</main>

@if(isset($result) && $result['status'] === 'ok')
<section class="mx-auto w-full max-w-7xl px-6 pb-12 sm:px-8 lg:px-10">
    <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-[0.16em] text-brand-700 dark:text-brand-300">{{ __('Step 2') }}</p>
            <h2 class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ __('Equipment that can carry the load') }}</h2>
        </div>
        @if(\Illuminate\Support\Facades\Route::has('ai.chat'))
            <a href="{{ route('ai.chat', ['prompt' => __('Explain this solar sizing estimate in plain language. Identify assumptions and practical caveats, then suggest what I should check before renting equipment. Daily energy: :daily Wh; panel power: :panel W; battery capacity: :battery Wh; inverter power: :inverter W.', ['daily' => number_format($result['daily_wh'], 0), 'panel' => number_format($result['panel_w'], 0), 'battery' => number_format($result['battery_wh'], 0), 'inverter' => number_format($result['inverter_w'], 0)])]) }}" class="inline-flex min-h-10 items-center rounded-lg border border-brand-300 px-4 py-2 text-theme-sm font-semibold text-brand-800 hover:bg-brand-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:border-brand-500/40 dark:text-brand-300 dark:hover:bg-brand-500/10">
                {{ __('Ask AI to explain this estimate') }}
            </a>
        @endif
    </div>

    @if(isset($result['matches']) && $result['matches']->isNotEmpty())
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach($result['matches'] as $item)
                <article class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-theme-sm transition-transform hover:-translate-y-1 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="h-32 bg-gray-100 dark:bg-gray-900">
                        <img src="{{ $item->image }}" alt="" class="size-full object-cover">
                    </div>
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-theme-xs text-gray-500">{{ $item->brand }} {{ $item->model }}</p>
                                <h3 class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $item->name }}</h3>
                            </div>
                            <span class="rounded-full bg-success-50 px-2.5 py-1 text-[11px] font-semibold text-success-700 dark:bg-success-500/10 dark:text-success-300">{{ __('Fits') }}</span>
                        </div>
                        <div class="mt-5 grid grid-cols-2 gap-3 text-theme-xs">
                            <div class="rounded-xl bg-gray-50 p-3 dark:bg-white/5">
                                <span class="text-gray-500">{{ __('Capacity') }}</span>
                                <strong class="mt-1 block text-gray-900 dark:text-white">{{ $item->energyProfile?->capacity_wh ?? '—' }} Wh</strong>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3 dark:bg-white/5">
                                <span class="text-gray-500">{{ __('Power') }}</span>
                                <strong class="mt-1 block text-gray-900 dark:text-white">{{ $item->energyProfile?->power_watts ?? $item->energyProfile?->max_output ?? '—' }} W</strong>
                            </div>
                        </div>

                        @if(isset($item->price_per_day))
                            <p class="mt-4 text-theme-sm font-semibold text-gray-900 dark:text-white">{{ __('Estimated daily cost') }} : {{ number_format((float) $item->price_per_day, 2, ',', ' ') }} TND</p>
                        @endif

                        @if($item->energyProfile?->co2_avoided_kg_day)
                            <div class="mt-4 rounded-xl bg-success-50 p-3 dark:bg-success-500/10 dark:border-success-500/30 border border-success-200">
                                <span class="text-[10px] uppercase text-success-700 dark:text-success-300">{{ __('CO₂ avoided (est.)') }}</span>
                                <strong class="mt-1 block text-lg text-success-800 dark:text-success-200">{{ number_format($item->energyProfile->co2_avoided_kg_day, 3) }} kg/day</strong>
                                <span class="text-[9px] text-success-600 dark:text-success-400">{{ __('Based on avg 4.5 kWh/m²/day · 0.42 kg CO₂/kWh') }}</span>
                            </div>
                        @endif

                        <a href="{{ route('front.equipment.show', $item->id) }}" class="mt-5 inline-flex font-semibold text-brand-700 hover:underline dark:text-brand-300">{{ __('View details') }} →</a>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="rounded-3xl border border-warning-200 bg-warning-50 p-6 text-theme-sm text-warning-900 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-100">
            {{ __('No published listing matches both your capacity and power needs yet. Try lowering the quantity or browse the full catalogue.') }}
            <a class="font-semibold underline" href="{{ route('front.catalog') }}">{{ __('Open catalogue') }}</a>
        </div>
    @endif
</section>
@endif
@endsection