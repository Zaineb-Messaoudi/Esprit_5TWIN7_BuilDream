@extends('layouts.front')

@section('content')
    <section class="relative isolate overflow-hidden bg-gray-950 text-white">
        <div class="pointer-events-none absolute inset-0 -z-20 bg-linear-to-br from-gray-950 via-brand-950 to-gray-900" aria-hidden="true"></div>
        <div class="hero-energy-grid pointer-events-none absolute inset-0 -z-10 opacity-30" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -end-52 -top-60 -z-10 size-[42rem] rounded-full bg-brand-400/15 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-80 start-1/4 -z-10 size-[38rem] rounded-full bg-warning-400/10 blur-3xl" aria-hidden="true"></div>

        <div class="relative mx-auto grid w-full max-w-7xl items-center gap-10 px-6 pb-10 pt-12 sm:px-8 sm:pb-14 sm:pt-16 lg:min-h-[680px] lg:grid-cols-[1.05fr_0.95fr] lg:gap-10 lg:px-10 lg:py-16">
            <div class="relative z-10 max-w-2xl lg:py-10">
                <p class="inline-flex items-center gap-2 rounded-full border border-warning-300/30 bg-warning-300/10 px-3.5 py-2 text-theme-xs font-semibold uppercase tracking-[0.18em] text-warning-200">
                    <span class="size-2 rounded-full bg-warning-300 shadow-theme-sm" aria-hidden="true"></span>{{ __('A shared-energy marketplace for everyone') }}
                </p>
                <h1 class="mt-7 max-w-2xl text-pretty text-title-lg font-semibold leading-[0.98] tracking-[-0.045em] text-white sm:text-title-xl xl:text-title-xl">
                    {{ __('Clean energy') }}<br class="hidden sm:block">
                    <span class="hero-title-accent">{{ __('goes further when we share it.') }}</span>
                </h1>
                <p class="mt-6 max-w-xl text-lg leading-8 text-gray-300 sm:text-theme-xl">
                    {{ __('SolarShare brings renters, equipment owners and local communities together to make renewable energy easier to access, share and keep in use.') }}
                </p>

                <form action="{{ route('front.catalog') }}" method="GET" class="mt-7 grid max-w-2xl gap-2 rounded-2xl border border-white/15 bg-white/5 p-2 backdrop-blur sm:grid-cols-[1fr_0.8fr_auto]">
                    <label class="sr-only" for="home-search">{{ __('Search equipment') }}</label>
                    <input id="home-search" type="search" name="q" placeholder="{{ __('What kind of power do you need?') }}" class="min-h-11 rounded-xl border border-transparent bg-transparent px-3 text-theme-sm text-white placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-300/30" />
                    <label class="sr-only" for="home-category">{{ __('Equipment category') }}</label>
                    <select id="home-category" name="category" class="min-h-11 rounded-xl border border-white/10 bg-gray-900 px-3 text-theme-sm text-white focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-300/30">
                        <option value="">{{ __('All equipment types') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ __($category->name) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="button-base button-primary min-h-11 px-5 text-theme-sm">{{ __('Search') }}</button>
                </form>

                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('front.catalog') }}" class="button-base button-primary min-h-12 px-5 text-theme-sm">
                        {{ __('Explore the catalog') }} <span aria-hidden="true">↗</span>
                    </a>
                    <a href="{{ route('front.owners') }}" class="button-base min-h-12 border border-white/25 px-5 text-theme-sm text-white hover:bg-white/10 focus-visible:ring-brand-300">
                        {{ __('Share your equipment') }}
                    </a>
                </div>

                <div class="mt-7 flex flex-wrap items-center gap-x-5 gap-y-2 text-theme-sm text-gray-300">
                    <span class="font-medium text-gray-400">{{ __('Explore:') }}</span>
                    @foreach ($categories as $category)
                        <a href="{{ route('front.catalog', ['category' => $category->id]) }}" class="inline-flex min-h-11 items-center gap-2 rounded-lg py-2 text-white transition-colors hover:text-warning-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-300 motion-reduce:transition-none">
                            <span class="text-warning-300" aria-hidden="true">{{ $category->icon }}</span>{{ __($category->name) }}
                        </a>
                    @endforeach
                </div>

                <div class="mt-8 flex flex-wrap items-center justify-between gap-3 border-t border-white/15 pt-6">
                    <p class="max-w-sm text-theme-sm leading-6 text-gray-300"><span class="font-semibold text-white">{{ __('Power for real-life plans.') }}</span> {{ __('A few days of access can beat another device gathering dust.') }}</p>
                    <a href="#how-it-works" class="inline-flex min-h-11 items-center gap-2 rounded-lg text-theme-sm font-semibold text-warning-200 hover:text-warning-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-warning-300">
                        {{ __('See how it works') }} <span aria-hidden="true">↓</span>
                    </a>
                </div>
            </div>

            <div class="relative mx-auto w-full max-w-2xl py-4 sm:py-8 lg:py-0">
                <div class="hero-energy-orbit absolute inset-[4%] rounded-full border border-brand-200/20" aria-hidden="true"></div>
                <div class="hero-energy-orbit hero-energy-orbit-delayed absolute inset-[13%] rounded-full border border-warning-200/20" aria-hidden="true"></div>
                <div class="absolute inset-8 rounded-full bg-brand-300/15 blur-3xl" aria-hidden="true"></div>
                <div class="relative mx-auto max-w-xl">
                    <svg class="pointer-events-none absolute inset-0 z-10 size-full overflow-visible" viewBox="0 0 600 500" fill="none" aria-hidden="true">
                        <path d="M58 342C126 243 184 425 259 329s107-186 180-100 74 155 116 67" class="hero-energy-route" />
                        <circle cx="58" cy="342" r="5" fill="currentColor" class="text-warning-300" />
                        <circle cx="439" cy="229" r="5" fill="currentColor" class="text-brand-300" />
                        <circle cx="555" cy="296" r="5" fill="currentColor" class="text-warning-300" />
                    </svg>
                    <div class="relative z-0 rotate-2 rounded-[2.5rem] border border-white/20 bg-linear-to-br from-white/20 to-white/5 p-2 shadow-2xl shadow-black/40 backdrop-blur-sm sm:p-3">
                        <div class="overflow-hidden rounded-[2rem] bg-brand-50">
                            <img src="{{ asset('images/front/battery-station.svg') }}" alt="{{ __('Illustration of a portable clean-energy battery station') }}" width="800" height="600" fetchpriority="high" class="aspect-4/3 w-full object-cover" />
                        </div>
                    </div>

                    <div class="absolute start-2 top-[8%] z-20 max-w-[13rem] rounded-2xl border border-white/15 bg-gray-900/90 p-3 shadow-xl backdrop-blur sm:-start-2 sm:p-4">
                        <span class="flex size-9 items-center justify-center rounded-xl bg-warning-300/15 text-lg text-warning-200" aria-hidden="true">☀</span>
                        <p class="mt-3 text-theme-xs font-medium uppercase tracking-wider text-gray-400">{{ __('One battery') }}</p>
                        <p class="mt-1 text-theme-sm font-semibold text-white">{{ __('Many new adventures') }}</p>
                    </div>
                    <div class="absolute end-2 bottom-[7%] z-20 max-w-[15rem] rounded-2xl border border-white/15 bg-gray-900/95 p-3 shadow-xl backdrop-blur sm:-end-2 sm:p-4">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('images/front/solar-panel.svg') }}" alt="" width="64" height="48" loading="lazy" class="size-12 rounded-xl bg-white object-cover" />
                            <div>
                                <p class="text-theme-xs text-gray-400">{{ __('THE SHARED-POWER LOOP') }}</p>
                                <p class="mt-1 text-theme-sm font-semibold text-white">{{ __('Borrow. Use. Return.') }}</p>
                            </div>
                        </div>
                    </div>
                    <span class="absolute end-2 top-[18%] z-20 hidden rounded-full border border-warning-300/30 bg-warning-300 px-3 py-1.5 text-theme-xs font-bold uppercase tracking-wider text-warning-950 shadow-theme-lg sm:block sm:end-4">{{ __('Power in good company') }}</span>
                </div>
                <p class="mt-5 text-center text-theme-xs font-medium uppercase tracking-[0.2em] text-gray-400">{{ __('Illustration · SolarShare concept') }}</p>
            </div>
        </div>

        <div class="relative border-t border-white/10 bg-black/20">
            <div class="mx-auto grid w-full max-w-7xl grid-cols-1 divide-y divide-white/10 px-6 sm:grid-cols-3 sm:divide-x sm:divide-y-0 sm:px-8 lg:px-10">
                <div class="flex items-center gap-3 py-4 sm:px-6 sm:first:ps-0">
                    <span class="text-xl text-warning-300" aria-hidden="true">01</span>
                    <p class="text-theme-sm font-medium text-gray-200">{{ __('Find power near you') }}</p>
                </div>
                <div class="flex items-center gap-3 py-4 sm:px-6">
                    <span class="text-xl text-warning-300" aria-hidden="true">02</span>
                    <p class="text-theme-sm font-medium text-gray-200">{{ __('Rent only for the days you need') }}</p>
                </div>
                <div class="flex items-center gap-3 py-4 sm:px-6 sm:last:pe-0">
                    <span class="text-xl text-warning-300" aria-hidden="true">03</span>
                    <p class="text-theme-sm font-medium text-gray-200">{{ __('Keep useful gear in motion') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Savings calculator --}}
    <section id="calculator" class="mx-auto w-full max-w-6xl scroll-mt-24 px-6 py-14 sm:px-8 sm:py-16 lg:px-10" aria-labelledby="calc-title">
        <div class="relative isolate grid gap-8 overflow-hidden rounded-[2rem] bg-linear-to-br from-brand-950 via-gray-950 to-gray-900 p-7 text-white shadow-theme-xl sm:p-10 lg:grid-cols-[1fr_0.85fr] lg:gap-14"
            x-data="{
                items: [
                    { n: @js(__('Battery 1000 Wh')), d: 18, b: 1800 },
                    { n: @js(__('Solar panel 200 W')), d: 9, b: 650 },
                    { n: @js(__('Wind turbine 400 W')), d: 14, b: 1100 }
                ],
                i: 0, days: 3,
                get it() { return this.items[this.i] },
                get rent() { return this.it.d * this.days },
                get saved() { return this.it.b - this.rent }
            }">
            <div class="relative">
                <p class="text-theme-xs font-semibold uppercase tracking-[0.2em] text-warning-300">{{ __('A smarter way to power your plans') }}</p>
                <h2 id="calc-title" class="mt-3 text-title-sm font-semibold text-white">{{ __('Rent the energy. Skip the extra stuff.') }}</h2>
                <p class="mt-3 max-w-xl leading-7 text-gray-300">{{ __('See how much you save by renting for a few days instead of buying.') }}</p>
                <div class="mt-6 space-y-5">
                    <div>
                        <label for="calc-item" class="mb-1.5 block text-theme-sm font-medium text-gray-200">{{ __('Equipment') }}</label>
                        <select id="calc-item" x-model.number="i" class="min-h-11 w-full rounded-xl border border-white/15 bg-gray-900 px-3 text-theme-sm text-white focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-300/30">
                            <template x-for="(item, idx) in items" :key="idx"><option :value="idx" x-text="item.n"></option></template>
                        </select>
                    </div>
                    <div>
                        <label for="calc-days" class="mb-1.5 flex justify-between text-theme-sm font-medium text-gray-200"><span>{{ __('Rental length') }}</span><span x-text="days + ' {{ __('days') }}'"></span></label>
                        <input id="calc-days" type="range" min="1" max="14" x-model.number="days" class="w-full accent-warning-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-warning-300 focus-visible:ring-offset-2 focus-visible:ring-offset-gray-950" />
                    </div>
                </div>
            </div>
            <div class="relative flex flex-col justify-center overflow-hidden rounded-3xl border border-white/10 bg-white/5 p-7 backdrop-blur-sm sm:p-8" aria-live="polite">
                <div class="pointer-events-none absolute -end-16 -top-20 size-52 rounded-full border border-brand-300/20" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -end-7 -top-11 size-36 rounded-full border border-warning-300/20" aria-hidden="true"></div>
                <p class="relative text-theme-sm text-gray-300">{{ __('Your rental total') }}</p>
                <p class="relative mt-1 text-title-md font-semibold text-white"><span x-text="rent"></span> <span class="text-theme-sm font-medium text-gray-300">TND</span></p>
                <div class="relative my-5 border-t border-white/15"></div>
                <p class="relative text-theme-sm text-gray-300">{{ __('Estimated saved vs buying') }}</p>
                <p class="relative mt-1 text-title-sm font-semibold text-brand-300"><span x-text="saved"></span> <span class="text-theme-sm font-medium">{{ __('TND stays in your pocket') }}</span></p>
                <p class="relative mt-5 text-theme-xs text-gray-400">{{ __('Illustrative estimate based on typical purchase prices.') }}</p>
            </div>
        </div>
    </section>

    @foreach (config('front.home') as $section)
        <x-front.section :s="$section" />
    @endforeach
@endsection
