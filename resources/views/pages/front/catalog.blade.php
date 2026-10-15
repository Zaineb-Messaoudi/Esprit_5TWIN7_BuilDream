@extends('layouts.front')

@section('content')
    @php
        $field = 'min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
        $sorts = [
            'newest' => __('Newest'),
            'name_asc' => __('Name: A to Z'),
            'price_asc' => __('Price: low to high'),
            'price_desc' => __('Price: high to low'),
            'power_desc' => __('Power: high to low'),
            'capacity_desc' => __('Capacity: high to low'),
        ];
    @endphp

    <section class="relative isolate overflow-hidden bg-gray-950 text-white">
        <div class="pointer-events-none absolute inset-0 -z-10 bg-linear-to-br from-gray-950 via-brand-950 to-gray-900" aria-hidden="true"></div>
        <div class="hero-energy-grid pointer-events-none absolute inset-0 -z-10 opacity-25" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -end-24 -top-36 -z-10 size-96 rounded-full bg-warning-400/10 blur-3xl" aria-hidden="true"></div>
        <div class="relative mx-auto flex w-full max-w-7xl flex-col justify-between gap-8 px-6 py-12 sm:px-8 sm:py-16 md:flex-row md:items-end lg:px-10">
            <div class="max-w-2xl">
                <p class="text-theme-xs font-semibold uppercase tracking-[0.2em] text-warning-300">{{ __('SolarShare / open catalogue') }}</p>
                <h1 class="mt-4 max-w-xl text-title-lg font-semibold tracking-tight text-white sm:text-title-xl">{{ __('Power for the plan you already have.') }}</h1>
                <p class="mt-4 max-w-xl text-lg leading-7 text-gray-300">{{ __('Good gear, shared locally. Filter by what your plans need, not what a salesperson wants to sell.') }}</p>
                <div class="mt-7 flex flex-wrap gap-2 text-theme-xs font-medium text-gray-200">
                    <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1.5">{{ __('Local owners') }}</span>
                    <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1.5">{{ __('Daily rental') }}</span>
                    <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1.5">{{ __('Energy details first') }}</span>
                </div>
            </div>
            <div class="relative overflow-hidden rounded-3xl border border-white/15 bg-white/5 p-5 backdrop-blur-sm md:max-w-xs">
                <div class="absolute -end-8 -top-10 size-32 rounded-full bg-warning-300/20 blur-2xl" aria-hidden="true"></div>
                <p class="relative text-4xl font-semibold tracking-tight text-warning-200">{{ $equipment->count() }}</p>
                <p class="relative mt-1 text-theme-sm leading-5 text-gray-300">{{ __('ways to borrow useful clean-energy equipment today.') }}</p>
            </div>
        </div>
    </section>

    <x-front.category-rail :categories="$categories" />

    <div class="mx-auto grid w-full max-w-7xl gap-8 px-6 py-8 sm:px-8 sm:py-12 lg:px-10" x-data="{ filtersOpen: true }" :class="filtersOpen ? 'lg:grid-cols-[17rem_1fr]' : 'lg:grid-cols-1'">
        {{-- Filters --}}
        <aside x-show="filtersOpen" x-cloak aria-label="{{ __('Filters') }}" class="lg:sticky lg:top-24 lg:self-start">
            <form method="GET" action="{{ route('front.catalog') }}" class="relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <input type="hidden" name="sort" value="{{ $filters['sort'] ?? 'newest' }}" />
                <div class="absolute inset-x-0 top-0 h-1 bg-linear-to-r from-brand-500 via-brand-300 to-warning-400" aria-hidden="true"></div>
                <div class="mb-5">
                    <p class="text-theme-xs font-semibold uppercase tracking-[0.16em] text-brand-700 dark:text-brand-300">{{ __('Search the network') }}</p>
                    <h2 class="mt-1 text-lg font-semibold tracking-tight text-gray-900 dark:text-white">{{ __('Refine your search') }}</h2>
                </div>
                <div>
                    <label for="q" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Search') }}</label>
                    <input id="q" type="search" name="q" autocomplete="off" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Name, brand, technology or city…') }}" class="{{ $field }}" />
                </div>
                <div class="mt-4">
                    <div>
                        <label for="brand" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Brand') }}</label>
                        <input id="brand" type="search" name="brand" autocomplete="off" value="{{ $filters['brand'] ?? '' }}" placeholder="{{ __('e.g. Voltix') }}" class="{{ $field }}" />
                    </div>
                </div>
                <div class="mt-4">
                    <div>
                        <label for="category" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Category') }}</label>
                        <select id="category" name="category" class="{{ $field }}">
                            <option value="">{{ __('All categories') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((int) ($filters['category'] ?? 0) === $category->id)>{{ __($category->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <label for="technology" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Technology') }}</label>
                    <select id="technology" name="technology" class="{{ $field }}">
                        <option value="">{{ __('Any technology') }}</option>
                        @foreach ($technologies as $technology)
                            <option value="{{ $technology }}" @selected(($filters['technology'] ?? '') === $technology)>{{ __($technology) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mt-5 border-t border-gray-200 pt-5 dark:border-gray-800">
                    <p class="mb-4 text-theme-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">{{ __('Energy parameters') }}</p>
                <div class="grid grid-cols-1 gap-3 xsm:grid-cols-2">
                    <div>
                        <label for="min_price" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Min price/day') }}</label>
                        <input id="min_price" type="number" min="0" step="1" name="min_price" value="{{ $filters['min_price'] ?? '' }}" class="{{ $field }}" />
                    </div>
                    <div>
                        <label for="max_price" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Max price/day') }}</label>
                        <input id="max_price" type="number" min="0" step="1" name="max_price" value="{{ $filters['max_price'] ?? '' }}" class="{{ $field }}" />
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-3 xsm:grid-cols-2">
                    <div>
                        <label for="min_power" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Min power (W)') }}</label>
                        <input id="min_power" type="number" min="0" step="50" name="min_power" value="{{ $filters['min_power'] ?? '' }}" class="{{ $field }}" />
                    </div>
                    <div>
                        <label for="max_power" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Max power (W)') }}</label>
                        <input id="max_power" type="number" min="0" step="50" name="max_power" value="{{ $filters['max_power'] ?? '' }}" class="{{ $field }}" />
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-3 xsm:grid-cols-2">
                    <div>
                        <label for="min_capacity" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Min capacity (Wh)') }}</label>
                        <input id="min_capacity" type="number" min="0" step="100" name="min_capacity" value="{{ $filters['min_capacity'] ?? '' }}" class="{{ $field }}" />
                    </div>
                    <div>
                        <label for="max_capacity" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Max capacity (Wh)') }}</label>
                        <input id="max_capacity" type="number" min="0" step="100" name="max_capacity" value="{{ $filters['max_capacity'] ?? '' }}" class="{{ $field }}" />
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-3 xsm:grid-cols-2">
                    <div>
                        <label for="condition" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Condition') }}</label>
                        <select id="condition" name="condition" class="{{ $field }}">
                            <option value="">{{ __('Any condition') }}</option>
                            @foreach ($conditions as $condition)
                                <option value="{{ $condition }}" @selected(($filters['condition'] ?? '') === $condition)>{{ __($condition) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="status" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Availability') }}</label>
                        <select id="status" name="status" class="{{ $field }}">
                            <option value="">{{ __('Any status') }}</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ __(ucfirst($status)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <label for="location" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Location') }}</label>
                    <input id="location" type="search" name="location" autocomplete="off" value="{{ $filters['location'] ?? '' }}" placeholder="{{ __('City or neighbourhood') }}" class="{{ $field }}" />
                </div>
                </div>
                <div class="relative flex flex-col gap-2 pt-5 sm:flex-row sm:items-center">
                    <button type="submit" class="button-base button-primary min-h-11 flex-1 px-4 text-theme-sm">{{ __('Apply filters') }}</button>
                    <a href="{{ route('front.catalog') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg text-theme-sm font-medium text-gray-500 hover:text-gray-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-gray-400 dark:hover:text-white">{{ __('Reset filters') }}</a>
                </div>
            </form>
        </aside>

        {{-- Results --}}
        <section id="catalog-results-title" aria-labelledby="catalog-results-heading">
            <div class="mb-6 flex flex-wrap items-end justify-between gap-4 border-b border-gray-200 pb-4 dark:border-gray-800">
                <div>
                    <p class="text-theme-xs font-semibold uppercase tracking-[0.16em] text-brand-700 dark:text-brand-300">{{ __('Find something useful') }}</p>
                    <p id="catalog-results-heading" role="status" class="mt-1 text-theme-sm text-gray-600 dark:text-gray-400">
                        {{ trans_choice('{0} No equipment found|{1} :count item found|[2,*] :count items found', $equipment->total()) }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="filtersOpen = !filtersOpen" :aria-expanded="filtersOpen" class="inline-flex min-h-10 items-center rounded-full border border-gray-300 bg-white px-3.5 text-theme-xs font-semibold text-gray-700 shadow-theme-xs transition hover:border-brand-400 hover:text-brand-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        <span x-text="filtersOpen ? '{{ __('Hide filters') }}' : '{{ __('Show filters') }}'"></span>
                    </button>
                    <form method="GET" action="{{ route('front.catalog') }}" class="flex items-center gap-2">
                        @foreach (['q', 'brand', 'category', 'min_price', 'max_price', 'min_power', 'max_power', 'min_capacity', 'max_capacity', 'condition', 'status', 'technology', 'location'] as $key)
                            @if (filled($filters[$key] ?? null))
                                <input type="hidden" name="{{ $key }}" value="{{ $filters[$key] }}" />
                            @endif
                        @endforeach
                        <label for="sort" class="sr-only">{{ __('Sort by') }}</label>
                        <select id="sort" name="sort" @change="$event.target.form.submit()" class="min-h-10 rounded-full border border-gray-300 bg-white px-3.5 text-theme-xs font-semibold text-gray-700 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            @foreach ($sorts as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['sort'] ?? 'newest') === $value)>{{ __('Sort: ') }}{{ $label }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
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
                <div class="mt-8">{{ $equipment->links() }}</div>
            @endif
        </section>
    </div>
@endsection
