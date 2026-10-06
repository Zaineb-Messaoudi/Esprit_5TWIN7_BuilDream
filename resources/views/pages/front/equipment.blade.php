@extends('layouts.front')

@section('content')
    @php
        $p = $item->energyProfile;
        $available = $item->status === 'available';
        $imageSrc = filter_var($item->image, FILTER_VALIDATE_URL) ? $item->image : asset($item->image);
        $specs = [
            __('Power') => $p->power_watts.' W',
            __('Capacity') => $p->capacity_wh ? $p->capacity_wh.' Wh' : '—',
            __('Voltage') => $p->voltage,
            __('Technology') => $p->technology ? __($p->technology) : '—',
            __('Condition') => $item->condition ? __(ucfirst($item->condition)) : '—',
            __('City') => $item->location,
        ];
    @endphp

    <div class="mx-auto w-full max-w-7xl px-6 py-8 sm:px-8 sm:py-12 lg:px-10">
        <nav aria-label="{{ __('Breadcrumb') }}" class="text-theme-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center hover:text-gray-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:hover:text-white">{{ __('Home') }}</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('front.catalog') }}" class="inline-flex min-h-11 items-center hover:text-gray-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:hover:text-white">{{ __('Catalog') }}</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page" class="text-gray-800 dark:text-white/90">{{ __($item->name) }}</span>
        </nav>

        <div class="mt-7 grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_24rem] xl:gap-12">
            <div class="min-w-0">
                <div class="relative overflow-hidden rounded-[2rem] border border-gray-200 bg-brand-50 shadow-theme-md dark:border-gray-800">
                    <img src="{{ $imageSrc }}" alt="{{ __($item->name) }}" width="800" height="600" fetchpriority="high" class="aspect-16/10 w-full object-cover" />
                    <span class="absolute start-5 top-5 inline-flex items-center gap-2 rounded-full border border-white/60 bg-white/95 px-3 py-2 text-theme-xs font-semibold text-brand-900 shadow-theme-sm">
                        <span class="size-2 rounded-full {{ $available ? 'bg-success-500' : 'bg-warning-500' }}" aria-hidden="true"></span>{{ $available ? __('Available') : __(ucfirst($item->status)) }}
                    </span>
                    <span class="absolute bottom-5 end-5 rounded-full bg-gray-950/85 px-3 py-2 text-theme-xs font-medium text-white backdrop-blur">{{ __('SolarShare · illustrative listing') }}</span>
                </div>
                <div class="mt-7 flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-theme-xs font-semibold uppercase tracking-[0.16em] text-brand-700 dark:text-brand-300">{{ __($item->category->name) }} <span class="px-1 text-warning-500" aria-hidden="true">/</span> {{ $item->location }}</p>
                        <h1 class="mt-2 text-title-sm font-semibold tracking-tight text-gray-950 dark:text-white sm:text-title-md">{{ __($item->name) }}</h1>
                        <p class="mt-2 text-theme-sm text-gray-500 dark:text-gray-400">{{ $item->brand }} {{ $item->model }} · {{ __('shared by') }} {{ $item->owner }}</p>
                    </div>
                    <p class="rounded-2xl bg-brand-50 px-4 py-3 text-end dark:bg-brand-500/10">
                        <span class="block text-theme-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Starting at') }}</span>
                        <span class="text-xl font-semibold text-brand-800 dark:text-brand-200">{{ number_format($item->price_per_day, 0) }} {{ __('TND') }}</span>
                        <span class="text-theme-xs text-gray-500 dark:text-gray-400">/ {{ __('day') }}</span>
                    </p>
                </div>
                <p class="mt-4 max-w-2xl leading-7 text-gray-600 dark:text-gray-400">{{ __($item->category->description) }}</p>

                <div class="mt-10 flex items-end justify-between gap-3">
                    <div>
                        <p class="text-theme-xs font-semibold uppercase tracking-[0.16em] text-brand-700 dark:text-brand-300">{{ __('The useful details') }}</p>
                        <h2 class="mt-2 text-title-sm font-semibold tracking-tight text-gray-900 dark:text-white">{{ __('Energy profile') }}</h2>
                    </div>
                    <span class="hidden text-theme-xs text-gray-500 dark:text-gray-400 sm:inline">{{ __('Know your power before you go') }}</span>
                </div>
                <dl class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3">
                    @foreach ($specs as $label => $value)
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                            <dt class="text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                            <dd class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
                <section class="mt-8 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]" aria-labelledby="availability-preview-title">
                    <div class="flex flex-wrap items-end justify-between gap-2">
                        <div><p class="text-theme-xs font-semibold uppercase tracking-[0.16em] text-brand-700 dark:text-brand-300">{{ __('Plan ahead') }}</p><h2 id="availability-preview-title" class="mt-1 font-semibold text-gray-900 dark:text-white">{{ __('Availability preview') }}</h2></div>
                        <span class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('October 2026 · sample dates') }}</span>
                    </div>
                    <div class="mt-4 grid grid-cols-7 gap-2 text-center">
                        @foreach ([['9', true], ['10', true], ['11', true], ['12', false], ['13', false], ['14', false], ['15', false]] as [$day, $reserved])
                            <div class="rounded-xl px-1 py-2 {{ $reserved ? 'bg-warning-50 text-warning-800 dark:bg-warning-500/15 dark:text-warning-300' : 'bg-success-50 text-success-800 dark:bg-success-500/15 dark:text-success-300' }}"><span class="block text-[10px] uppercase text-gray-500 dark:text-gray-400">{{ __('Oct') }}</span><span class="mt-1 block text-sm font-semibold">{{ $day }}</span><span class="mt-1 block text-[9px]">{{ $reserved ? __('Booked') : __('Free') }}</span></div>
                        @endforeach
                    </div>
                    <p class="mt-3 text-[11px] text-gray-500 dark:text-gray-400">{{ __('Calendar dates are illustrative. Live availability and overlap checks are not connected.') }}</p>
                </section>
            </div>

            <aside class="lg:sticky lg:top-24 lg:self-start" aria-label="{{ __('Booking') }}"
                x-data="{
                    start: '', end: '', price: {{ $item->price_per_day }},
                    get days() { if (!this.start || !this.end) return 0; const d = (new Date(this.end) - new Date(this.start)) / 86400000 + 1; return d > 0 ? d : 0 },
                    get total() { return this.days * this.price }
                }">
                <div class="relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900 sm:p-7">
                    <div class="absolute inset-x-0 top-0 h-1.5 bg-linear-to-r from-brand-500 via-brand-300 to-warning-400" aria-hidden="true"></div>
                    <p class="text-theme-xs font-semibold uppercase tracking-[0.18em] text-brand-700 dark:text-brand-300">{{ __('Make a weekend of it') }}</p>
                    <p class="mt-3"><span class="text-title-sm font-semibold tracking-tight text-gray-950 dark:text-white">{{ number_format($item->price_per_day, 0) }} {{ __('TND') }}</span> <span class="text-theme-sm text-gray-500 dark:text-gray-400">/ {{ __('day') }}</span></p>

                    <div class="mt-5 grid grid-cols-1 gap-3 xsm:grid-cols-2">
                        <div>
                            <label for="start" class="mb-1.5 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Date from') }}</label>
                            <input id="start" type="date" name="start" autocomplete="off" x-model="start" class="min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        </div>
                        <div>
                            <label for="end" class="mb-1.5 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('To') }}</label>
                            <input id="end" type="date" name="end" autocomplete="off" x-model="end" :min="start" class="min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        </div>
                    </div>

                    <p class="mt-5 flex justify-between rounded-xl bg-brand-50 p-4 text-theme-sm text-gray-600 dark:bg-brand-500/10 dark:text-gray-300" aria-live="polite">
                        <span x-text="days + ' {{ __('day(s)') }}'"></span>
                        <span class="font-semibold text-brand-800 dark:text-brand-200"><span x-text="total"></span> {{ __('TND') }}</span>
                    </p>

                    @if ($available)
                        @auth
                            @if (auth()->user()->isBuyer())
                                <a href="{{ route('front.reserve', ['equipment' => $item->id]) }}" class="button-base button-primary mt-5 min-h-12 w-full text-theme-sm">{{ __('Preview booking flow') }}</a>
                            @else
                                <p class="mt-5 rounded-lg bg-brand-50 px-4 py-3 text-center text-theme-sm text-brand-800 dark:bg-brand-500/10 dark:text-brand-200">{{ __('Browse-only catalogue. Buyer access is required to continue.') }}</p>
                            @endif
                        @else
                            <p class="mt-5 rounded-lg bg-brand-50 px-4 py-3 text-center text-theme-sm text-brand-800 dark:bg-brand-500/10 dark:text-brand-200">{{ __('Browse-only catalogue. Sign in as a buyer to continue.') }}</p>
                        @endauth
                        <p class="mt-3 text-center text-theme-xs leading-5 text-gray-500 dark:text-gray-400">{{ __('Availability checks, reservations and payments are handled after buyer access.') }}</p>
                    @else
                        <p class="mt-5 rounded-lg bg-warning-50 px-4 py-3 text-theme-sm text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">{{ __('This equipment is in maintenance and cannot be reserved right now.') }}</p>
                    @endif
                </div>
            </aside>
        </div>

        @if ($related->isNotEmpty())
            <div class="mt-16 border-t border-gray-200 pt-10 dark:border-gray-800">
                <p class="text-theme-xs font-semibold uppercase tracking-[0.16em] text-brand-700 dark:text-brand-300">{{ __('Keep exploring') }}</p>
                <h2 class="mt-2 text-title-sm font-semibold tracking-tight text-gray-900 dark:text-white">{{ __('Similar equipment') }}</h2>
            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($related as $rel)
                    <x-front.equipment-card :item="$rel" />
                @endforeach
            </div>
            </div>
        @endif
    </div>
@endsection
