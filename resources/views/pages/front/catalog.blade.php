@extends('layouts.front')

@section('content')
    @php
        $field = 'min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
        $sorts = [
            'newest' => __('Newest'),
            'price_asc' => __('Price: low to high'),
            'price_desc' => __('Price: high to low'),
            'power_desc' => __('Power: high to low'),
        ];
    @endphp

    <section class="relative isolate overflow-hidden bg-gray-950 text-white">
        <div class="pointer-events-none absolute inset-0 -z-10 bg-linear-to-br from-gray-950 via-brand-950 to-gray-900" aria-hidden="true"></div>
        <div class="hero-energy-grid pointer-events-none absolute inset-0 -z-10 opacity-25" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -end-24 -top-36 -z-10 size-96 rounded-full bg-warning-400/10 blur-3xl" aria-hidden="true"></div>
        <div class="relative mx-auto flex w-full max-w-7xl flex-col justify-between gap-8 px-6 py-12 sm:px-8 sm:py-16 md:flex-row md:items-end lg:px-10">
            <div class="max-w-2xl">
                <p class="text-theme-xs font-semibold uppercase tracking-[0.2em] text-warning-300">{{ __('The SolarShare collection') }}</p>
                <h1 class="mt-4 text-title-lg font-semibold tracking-tight text-white sm:text-title-xl">{{ __('Find your kind of power.') }}</h1>
                <p class="mt-4 max-w-xl text-lg leading-7 text-gray-300">{{ __('Good gear, shared locally. Filter by what your plans need, not what a salesperson wants to sell.') }}</p>
            </div>
            <div class="flex items-center gap-3 border-t border-white/15 pt-5 md:border-s md:border-t-0 md:ps-6 md:pt-0">
                <span class="flex size-12 items-center justify-center rounded-2xl bg-warning-300/15 text-2xl text-warning-200" aria-hidden="true">⚡</span>
                <p class="max-w-40 text-theme-sm leading-5 text-gray-300">{{ __('Portable solar, batteries and small wind power.') }}</p>
            </div>
        </div>
    </section>

    <div class="mx-auto grid w-full max-w-7xl gap-8 px-6 py-8 sm:px-8 sm:py-12 lg:grid-cols-[17rem_1fr] lg:px-10">
        {{-- Filters --}}
        <aside aria-label="{{ __('Filters') }}" class="lg:sticky lg:top-24 lg:self-start">
            <form method="GET" action="{{ route('front.catalog') }}" class="relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <div class="absolute inset-x-0 top-0 h-1 bg-linear-to-r from-brand-500 via-brand-300 to-warning-400" aria-hidden="true"></div>
                <div class="mb-5 flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold tracking-tight text-gray-900 dark:text-white">{{ __('Refine your search') }}</h2>
                    <span class="text-xl text-brand-600 dark:text-brand-300" aria-hidden="true">☷</span>
                </div>
                <div>
                    <label for="q" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Search') }}</label>
                    <input id="q" type="search" name="q" autocomplete="off" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Name, brand or city…') }}" class="{{ $field }}" />
                </div>
                <div>
                    <label for="category" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Category') }}</label>
                    <select id="category" name="category" class="{{ $field }}">
                        <option value="">{{ __('All categories') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) ($filters['category'] ?? 0) === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-1 gap-3 xsm:grid-cols-2">
                    <div>
                        <label for="max_price" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Max price/day') }}</label>
                        <input id="max_price" type="number" min="0" step="1" name="max_price" value="{{ $filters['max_price'] ?? '' }}" class="{{ $field }}" />
                    </div>
                    <div>
                        <label for="min_power" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Min power (W)') }}</label>
                        <input id="min_power" type="number" min="0" step="50" name="min_power" value="{{ $filters['min_power'] ?? '' }}" class="{{ $field }}" />
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-3 xsm:grid-cols-2">
                    <div>
                        <label for="min_capacity" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Min capacity (Wh)') }}</label>
                        <input id="min_capacity" type="number" min="0" step="100" name="min_capacity" value="{{ $filters['min_capacity'] ?? '' }}" class="{{ $field }}" />
                    </div>
                    <div>
                        <label for="condition" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Condition') }}</label>
                        <select id="condition" name="condition" class="{{ $field }}">
                            <option value="">{{ __('Any condition') }}</option>
                            @foreach ($conditions as $condition)
                                <option value="{{ $condition }}" @selected(($filters['condition'] ?? '') === $condition)>{{ __($condition) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label for="location" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Location') }}</label>
                    <input id="location" type="search" name="location" autocomplete="off" value="{{ $filters['location'] ?? '' }}" placeholder="{{ __('City or neighbourhood') }}" class="{{ $field }}" />
                </div>
                <div>
                    <label for="sort" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Sort by') }}</label>
                    <select id="sort" name="sort" class="{{ $field }}">
                        @foreach ($sorts as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['sort'] ?? 'newest') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-2 pt-2">
                    <button type="submit" class="button-base button-primary min-h-11 flex-1 px-4 text-theme-sm">{{ __('Apply filters') }}</button>
                    <a href="{{ route('front.catalog') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg text-theme-sm font-medium text-gray-500 hover:text-gray-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-gray-400 dark:hover:text-white">{{ __('Reset filters') }}</a>
                </div>
            </form>
        </aside>

        {{-- Results --}}
        <section aria-labelledby="catalog-results-title">
            <div class="mb-6 flex flex-wrap items-end justify-between gap-3 border-b border-gray-200 pb-4 dark:border-gray-800">
                <div>
                    <p class="text-theme-xs font-semibold uppercase tracking-[0.16em] text-brand-700 dark:text-brand-300">{{ __('Find something useful') }}</p>
                    <p id="catalog-results-title" role="status" class="mt-1 text-theme-sm text-gray-600 dark:text-gray-400">
                        {{ trans_choice('{0} No equipment found|{1} :count item found|[2,*] :count items found', $equipment->count()) }}
                    </p>
                </div>
                <span class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Shared nearby · rented by the day') }}</span>
            </div>

            @if ($equipment->isEmpty())
                <div class="relative overflow-hidden rounded-3xl border border-dashed border-brand-300 bg-brand-50/70 p-10 text-center dark:border-brand-500/30 dark:bg-brand-500/5">
                    <span class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-white text-3xl shadow-theme-xs dark:bg-gray-900" aria-hidden="true">🔋</span>
                    <h2 class="mt-5 text-lg font-semibold text-gray-900 dark:text-white">{{ __('Nothing matches your search') }}</h2>
                    <p class="mx-auto mt-2 max-w-md text-theme-sm leading-6 text-gray-600 dark:text-gray-400">{{ __('Try removing a filter or searching for something broader.') }}</p>
                    <a href="{{ route('front.catalog') }}" class="button-base button-primary mt-6 px-5 py-2.5 text-theme-sm">{{ __('Clear filters') }}</a>
                </div>
            @else
                <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($equipment as $item)
                        <x-front.equipment-card :item="$item" />
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
