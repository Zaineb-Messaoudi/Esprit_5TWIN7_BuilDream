@extends('layouts.front')

@section('content')
    @php
        $val = fn (string $path, $default = null) => old($path, data_get($input, $path, $default));
        $card = 'rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-7';
        $field = 'mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
        $roles = [
            'generation' => [__('Generation (panels, turbines)'), 'W'],
            'storage' => [__('Storage (batteries)'), 'Wh'],
            'inverter' => [__('Inverters and power output'), 'W'],
        ];
    @endphp

    <section class="bg-gray-950 text-white">
        <div class="mx-auto max-w-7xl px-4 py-9 sm:px-8 lg:px-10">
            <p class="text-theme-xs font-semibold uppercase tracking-[0.2em] text-warning-300">{{ __('Energy planning') }}</p>
            <h1 class="mt-2 text-title-sm font-semibold sm:text-title-md">{{ __('Energy needs calculator') }}</h1>
            <p class="mt-2 max-w-3xl text-theme-sm text-gray-300">{{ __('Estimate the solar panels, battery and inverter you need for your appliances.') }}</p>
        </div>
    </section>

    <main class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-8 lg:px-10">
        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-error-200 bg-error-50 p-4 text-theme-sm text-error-800 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-200">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('front.energy-calculator.calculate') }}" class="{{ $card }} space-y-6">
            @csrf
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Your appliances') }}</h2>

            <div class="grid gap-3">
                @foreach ($appliances as $key => $appliance)
                    <div class="grid items-center gap-3 rounded-xl border border-gray-200 p-3 dark:border-gray-800 sm:grid-cols-[1fr_7rem_9rem]">
                        <label class="flex items-center gap-3 text-theme-sm font-medium text-gray-800 dark:text-gray-100">
                            <input type="checkbox" name="items[{{ $key }}][selected]" value="1" @checked($val("items.$key.selected")) class="size-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <span>{{ __($appliance['label']) }} <span class="text-gray-500">({{ $appliance['watts'] }} W)</span></span>
                        </label>
                        <label class="text-theme-xs text-gray-600 dark:text-gray-300">{{ __('Quantity') }}
                            <input type="number" min="1" max="50" name="items[{{ $key }}][quantity]" value="{{ $val("items.$key.quantity", 1) }}" class="{{ $field }}">
                        </label>
                        <label class="text-theme-xs text-gray-600 dark:text-gray-300">{{ __('Hours per day') }}
                            <input type="number" min="0" max="24" step="0.5" name="items[{{ $key }}][hours]" value="{{ $val("items.$key.hours") }}" placeholder="{{ $appliance['hours'] }}" class="{{ $field }}">
                        </label>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="text-theme-sm font-medium text-gray-800 dark:text-gray-100">{{ __('Days of autonomy') }}
                    <input type="number" min="0.5" max="5" step="0.5" name="autonomy_days" value="{{ $val('autonomy_days', 1) }}" class="{{ $field }}">
                </label>
                <label class="text-theme-sm font-medium text-gray-800 dark:text-gray-100">{{ __('Sun hours per day') }}
                    <input type="number" min="2" max="8" step="0.5" name="sun_hours" value="{{ $val('sun_hours', 5) }}" class="{{ $field }}">
                </label>
            </div>

            <button type="submit" class="button-base bg-brand-700 px-5 text-theme-sm font-semibold text-white hover:bg-brand-600 focus-visible:ring-brand-500">{{ __('Calculate my needs') }}</button>
        </form>

        @if ($result !== null)
            @if ($result['status'] !== 'ok')
                <div role="status" class="rounded-xl border border-warning-200 bg-warning-50 p-4 text-theme-sm text-warning-800 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-200">
                    {{ __('Select at least one appliance.') }}
                </div>
            @else
                <section class="{{ $card }}" aria-labelledby="needs-title">
                    <h2 id="needs-title" class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Your estimated needs') }}</h2>
                    <dl class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-xl bg-gray-50 p-4 dark:bg-white/5">
                            <dt class="text-theme-xs text-gray-500">{{ __('Daily consumption') }}</dt>
                            <dd class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ number_format($result['daily_wh'], 0, ',', ' ') }} Wh</dd>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-4 dark:bg-white/5">
                            <dt class="text-theme-xs text-gray-500">{{ __('Solar panel power') }}</dt>
                            <dd class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ number_format($result['panel_w'], 0, ',', ' ') }} W</dd>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-4 dark:bg-white/5">
                            <dt class="text-theme-xs text-gray-500">{{ __('Battery capacity') }}</dt>
                            <dd class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ number_format($result['battery_wh'], 0, ',', ' ') }} Wh</dd>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-4 dark:bg-white/5">
                            <dt class="text-theme-xs text-gray-500">{{ __('Inverter power') }}</dt>
                            <dd class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ number_format($result['inverter_w'], 0, ',', ' ') }} W</dd>
                        </div>
                    </dl>

                    <h3 class="mt-6 text-theme-sm font-semibold text-gray-900 dark:text-white">{{ __('Assumptions used') }}</h3>
                    <ul class="mt-2 list-disc space-y-1 ps-5 text-theme-xs text-gray-600 dark:text-gray-300">
                        <li>{{ __('Days of autonomy') }} : {{ $result['assumptions']['autonomy_days'] }}</li>
                        <li>{{ __('Sun hours per day') }} : {{ $result['assumptions']['sun_hours'] }}</li>
                        <li>{{ __('Inverter efficiency') }} : {{ $result['assumptions']['inverter_efficiency'] * 100 }} %</li>
                        <li>{{ __('Usable battery depth') }} : {{ $result['assumptions']['battery_depth_of_discharge'] * 100 }} %</li>
                        <li>{{ __('Panel system efficiency') }} : {{ $result['assumptions']['panel_system_efficiency'] * 100 }} %</li>
                        <li>{{ __('Inverter safety margin') }} : {{ ($result['assumptions']['inverter_margin'] - 1) * 100 }} %</li>
                    </ul>
                    <p class="mt-3 text-theme-xs text-gray-500">{{ __('This is an estimate based on typical appliance values. Check real power ratings before renting.') }}</p>
                </section>

                <section class="{{ $card }}" aria-labelledby="equipment-title">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 id="equipment-title" class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Suggested equipment') }}</h2>
                        <a href="{{ route('ai.chat', ['prompt' => __('Explain this solar sizing estimate in plain language. Identify assumptions and practical caveats, then suggest what I should check before renting equipment. Daily energy: :daily Wh; panel power: :panel W; battery capacity: :battery Wh; inverter power: :inverter W.', ['daily' => number_format($result['daily_wh'], 0), 'panel' => number_format($result['panel_w'], 0), 'battery' => number_format($result['battery_wh'], 0), 'inverter' => number_format($result['inverter_w'], 0)])]) }}" class="inline-flex min-h-10 items-center rounded-lg border border-brand-300 px-4 py-2 text-theme-sm font-semibold text-brand-800 hover:bg-brand-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:border-brand-500/40 dark:text-brand-300 dark:hover:bg-brand-500/10">
                            {{ __('Ask AI to explain this estimate') }}
                        </a>
                    </div>

                    @foreach ($roles as $role => [$title, $unit])
                        <h3 class="mt-5 text-theme-sm font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                        @forelse (($matches[$role] ?? []) as $row)
                            <div class="mt-2 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 p-3 text-theme-sm dark:border-gray-800">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $row['name'] }}</p>
                                    <p class="text-theme-xs text-gray-500">{{ $row['location'] }}</p>
                                </div>
                                <p class="text-gray-700 dark:text-gray-200">{{ $row['units'] }} × {{ number_format($row['unit_value'], 0, ',', ' ') }} {{ $unit }}</p>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ __('Estimated daily cost') }} : {{ number_format($row['daily_cost'], 2, ',', ' ') }} TND</p>
                            </div>
                        @empty
                            <p class="mt-2 text-theme-sm text-gray-500">{{ __('No published equipment covers this need yet.') }}</p>
                        @endforelse
                    @endforeach

                    <a href="{{ url('/catalog') }}" class="button-base mt-6 border border-gray-300 px-4 text-theme-sm text-gray-700 hover:bg-gray-50 focus-visible:ring-brand-500 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5">{{ __('Browse the catalogue') }}</a>
                </section>
            @endif
        @endif
    </main>
@endsection
