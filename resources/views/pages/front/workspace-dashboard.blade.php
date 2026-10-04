@extends('layouts.front')

@section('content')
    @php
        $ownerKpis = [
            ['label' => __('Total equipment'), 'value' => '12', 'icon' => 'leaf', 'detail' => __('Across all listings')],
            ['label' => __('Available now'), 'value' => '9', 'icon' => 'shield', 'detail' => __('Ready to be reserved')],
            ['label' => __('Active rentals'), 'value' => '3', 'icon' => 'clock', 'detail' => __('Currently with renters')],
            ['label' => __('Pending reservations'), 'value' => '2', 'icon' => 'calendar', 'detail' => __('Need your decision')],
            ['label' => __('Extension requests'), 'value' => '1', 'icon' => 'document', 'detail' => __('Waiting for a response')],
            ['label' => __('Revenue this month'), 'value' => '1,240 TND', 'icon' => 'wallet', 'detail' => __('From completed payments')],
            ['label' => __('Returns due this week'), 'value' => '2', 'icon' => 'calendar', 'detail' => __('Prepare for inspection')],
            ['label' => __('In maintenance'), 'value' => '1', 'icon' => 'tool', 'detail' => __('Temporarily unavailable')],
        ];
        $chartColors = ['#176b45', '#f4bd45', '#0ba5ec', '#f79009'];
        $ownerCharts = [
            [
                'title' => __('Revenue per month'),
                'description' => __('Illustrative paid revenue across the last six months.'),
                'options' => [
                    'chart' => ['type' => 'area', 'toolbar' => ['show' => false]],
                    'series' => [['name' => __('Revenue (TND)'), 'data' => [520, 760, 640, 980, 840, 1240]]],
                    'xaxis' => ['categories' => [__('May'), __('Jun'), __('Jul'), __('Aug'), __('Sep'), __('Oct')]],
                    'colors' => [$chartColors[0]],
                    'stroke' => ['curve' => 'smooth', 'width' => 3],
                    'fill' => ['type' => 'gradient', 'gradient' => ['opacityFrom' => 0.3, 'opacityTo' => 0.03]],
                    'dataLabels' => ['enabled' => false],
                ],
            ],
            [
                'title' => __('Reservations by status'),
                'description' => __('Illustrative distribution of recent requests.'),
                'options' => [
                    'chart' => ['type' => 'donut', 'toolbar' => ['show' => false]],
                    'series' => [2, 8, 1],
                    'labels' => [__('Pending'), __('Confirmed'), __('Cancelled')],
                    'colors' => [$chartColors[1], $chartColors[0], '#f04438'],
                    'legend' => ['position' => 'bottom'],
                    'plotOptions' => ['pie' => ['donut' => ['size' => '68%', 'labels' => ['show' => true, 'total' => ['show' => true, 'label' => __('Requests')]]]]],
                ],
            ],
            [
                'title' => __('Equipment status'),
                'description' => __('Illustrative availability across your listings.'),
                'options' => [
                    'chart' => ['type' => 'donut', 'toolbar' => ['show' => false]],
                    'series' => [9, 2, 3, 1],
                    'labels' => [__('Available'), __('Reserved'), __('Rented'), __('Maintenance')],
                    'colors' => [$chartColors[0], $chartColors[1], $chartColors[2], '#f04438'],
                    'legend' => ['position' => 'bottom'],
                    'plotOptions' => ['pie' => ['donut' => ['size' => '68%', 'labels' => ['show' => true, 'total' => ['show' => true, 'label' => __('Equipment')]]]]],
                ],
            ],
            [
                'title' => __('Rentals by equipment'),
                'description' => __('Illustrative rental volume for your top listings.'),
                'options' => [
                    'chart' => ['type' => 'bar', 'toolbar' => ['show' => false]],
                    'series' => [['name' => __('Rentals'), 'data' => [14, 11, 8, 6, 4]]],
                    'xaxis' => ['categories' => ['Portable battery 1000 Wh', 'Foldable solar panel', 'Power station 2000 Wh', 'Solar kit 200 W', 'Compact battery 500 Wh']],
                    'colors' => [$chartColors[0]],
                    'plotOptions' => ['bar' => ['horizontal' => true, 'borderRadius' => 4, 'barHeight' => '55%']],
                    'dataLabels' => ['enabled' => false],
                ],
            ],
        ];
        $ownerTodos = [
            ['title' => __('Confirm two reservation requests'), 'detail' => __('Check requested dates and equipment availability.'), 'count' => '2', 'route' => 'front.my-reservations', 'action' => __('Review')],
            ['title' => __('Answer an extension request'), 'detail' => __('A renter is asking for one more day.'), 'count' => '1', 'route' => 'front.my-extensions', 'action' => __('Decide')],
            ['title' => __('Prepare two equipment returns'), 'detail' => __('Returns due this week need an inspection.'), 'count' => '2', 'route' => 'front.my-inspections', 'action' => __('View inspections')],
        ];
        $ownerActivity = [
            ['title' => __('New reservation request'), 'detail' => __('Portable battery 1000 Wh · 9–11 Oct'), 'time' => __('Today · 10:42'), 'tone' => 'warning'],
            ['title' => __('Extension requested'), 'detail' => __('Foldable solar panel · +1 day'), 'time' => __('Today · 09:15'), 'tone' => 'brand'],
            ['title' => __('Payment received'), 'detail' => __('Power station 2000 Wh · 90 TND'), 'time' => __('Yesterday · 16:08'), 'tone' => 'success'],
            ['title' => __('Inspection completed'), 'detail' => __('Compact battery 500 Wh · no damage'), 'time' => __('Yesterday · 11:30'), 'tone' => 'success'],
        ];
        $stats = [
                ['label' => __('Active rentals'), 'value' => '1', 'icon' => 'clock', 'tone' => 'success'],
                ['label' => __('Upcoming reservations'), 'value' => '2', 'icon' => 'calendar', 'tone' => 'brand'],
                ['label' => __('Pending reservations'), 'value' => '1', 'icon' => 'document', 'tone' => 'warning'],
                ['label' => __('Total spent'), 'value' => '163.99 TND', 'icon' => 'wallet', 'tone' => 'brand'],
                ['label' => __('Total rental days'), 'value' => '9 days', 'icon' => 'calendar', 'tone' => 'success'],
                ['label' => __('Extensions awaiting owner'), 'value' => '1', 'icon' => 'clock', 'tone' => 'warning'],
            ];
        $buyerCharts = [
            [
                'title' => __('Spending per month'),
                'description' => __('Illustrative paid totals for the last 12 months.'),
                'options' => [
                    'chart' => ['type' => 'bar', 'toolbar' => ['show' => false]],
                    'series' => [['name' => __('Spend (TND)'), 'data' => [42, 86, 54, 112, 76, 64, 98, 38, 64, 21, 0, 0]]],
                    'xaxis' => ['categories' => [__('Nov'), __('Dec'), __('Jan'), __('Feb'), __('Mar'), __('Apr'), __('May'), __('Jun'), __('Jul'), __('Aug'), __('Sep'), __('Oct')]],
                    'colors' => [$chartColors[0]],
                    'plotOptions' => ['bar' => ['borderRadius' => 4, 'columnWidth' => '48%']],
                    'dataLabels' => ['enabled' => false],
                ],
            ],
            [
                'title' => __('Rentals by category'),
                'description' => __('The equipment types in your sample rental history.'),
                'options' => [
                    'chart' => ['type' => 'donut', 'toolbar' => ['show' => false]],
                    'series' => [4, 3, 2],
                    'labels' => [__('Batteries'), __('Solar panels'), __('Wind equipment')],
                    'colors' => [$chartColors[0], $chartColors[1], $chartColors[2]],
                    'legend' => ['position' => 'bottom'],
                ],
            ],
            [
                'title' => __('Reservations by status'),
                'description' => __('A quick view of pending, confirmed and cancelled requests.'),
                'options' => [
                    'chart' => ['type' => 'donut', 'toolbar' => ['show' => false]],
                    'series' => [2, 1, 1],
                    'labels' => [__('Pending'), __('Confirmed'), __('Cancelled')],
                    'colors' => [$chartColors[1], $chartColors[0], '#98a2b3'],
                    'legend' => ['position' => 'bottom'],
                ],
            ],
        ];
        $activities = [
                ['title' => __('Battery reservation'), 'detail' => __('Portable battery · 9–11 Oct · 54 TND'), 'status' => __('Upcoming'), 'tone' => 'brand'],
                ['title' => __('Solar panel rental'), 'detail' => __('Foldable panel · return due 4 Oct'), 'status' => __('Active'), 'tone' => 'success'],
                ['title' => __('Energy saved'), 'detail' => __('You borrowed instead of buying equipment'), 'status' => __('Nice work'), 'tone' => 'warning'],
            ];
        $tones = [
            'brand' => 'bg-brand-50 text-brand-800 dark:bg-brand-500/15 dark:text-brand-200',
            'warning' => 'bg-warning-50 text-warning-800 dark:bg-warning-500/15 dark:text-warning-200',
            'success' => 'bg-success-50 text-success-800 dark:bg-success-500/15 dark:text-success-200',
        ];
    @endphp

    <section class="relative isolate overflow-hidden bg-gray-950 text-white">
        <div class="pointer-events-none absolute inset-0 -z-20 bg-linear-to-br from-gray-950 via-brand-950 to-gray-900" aria-hidden="true"></div>
        <div class="hero-energy-grid pointer-events-none absolute inset-0 -z-10 opacity-25" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -end-40 -top-40 -z-10 size-[34rem] rounded-full bg-brand-400/15 blur-3xl" aria-hidden="true"></div>
        <div class="mx-auto flex w-full max-w-7xl flex-col gap-8 px-6 py-12 sm:px-8 sm:py-16 lg:flex-row lg:items-end lg:justify-between lg:px-10">
            <div>
                <p class="inline-flex items-center gap-2 rounded-full border border-brand-300/20 bg-white/5 px-3 py-1.5 text-theme-xs font-semibold uppercase tracking-[0.2em] text-brand-200"><span class="size-2 rounded-full bg-warning-300" aria-hidden="true"></span>{{ $owner ? __('Owner workspace') : __('Your SolarShare') }}</p>
                <h1 class="mt-4 max-w-3xl text-pretty text-title-sm font-semibold tracking-tight sm:text-title-md">
                    {{ $owner ? __('Make your equipment work harder, :name.', ['name' => $user->name]) : __('Your next adventure starts here, :name.', ['name' => $user->name]) }}
                </h1>
                <p class="mt-3 max-w-2xl text-gray-300">
                    {{ $owner ? __('A clear view of your listings, rental requests and equipment activity.') : __('Keep your borrowed gear, upcoming plans and clean-energy impact in one place.') }}
                </p>
            </div>
            <a href="{{ route($owner ? 'front.my-publish' : 'front.catalog') }}" class="button-base button-primary min-h-12 shrink-0 px-5 text-theme-sm">
                {{ $owner ? __('List equipment') : __('Find equipment') }}
                <span aria-hidden="true">{{ $owner ? '+' : '→' }}</span>
            </a>
        </div>
    </section>

    @if ($owner)
        @include('pages.front.partials.owner-nav')
    @else
        @include('pages.front.partials.buyer-nav')
    @endif

    <div class="mx-auto w-full max-w-7xl space-y-8 px-6 py-9 sm:px-8 sm:py-12 lg:px-10">
        @if ($owner)
            <div class="flex items-start gap-3 rounded-2xl border border-warning-200 bg-warning-50 px-4 py-4 text-theme-sm leading-6 text-warning-800 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-200" role="status">
                <span class="mt-0.5 text-lg" aria-hidden="true">✳</span>
                <p><span class="font-semibold">{{ __('Illustrative dashboard data.') }}</span> {{ __('Charts, KPI values and activity below are examples only. They are not live account or payment records.') }}</p>
            </div>

            <section aria-label="{{ __('Owner dashboard statistics') }}" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($ownerKpis as $stat)
                    <article class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                        <span class="absolute inset-x-0 top-0 h-1 bg-linear-to-r from-brand-600 to-warning-300" aria-hidden="true"></span>
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-theme-sm font-medium text-gray-500 dark:text-gray-400">{{ $stat['label'] }}</p>
                            <span class="flex size-10 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300" aria-hidden="true">
                                <x-front.icon :name="$stat['icon']" class="size-5" />
                            </span>
                        </div>
                        <p class="mt-5 text-title-sm font-semibold tabular-nums text-gray-900 dark:text-white">{{ $stat['value'] }}</p>
                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ $stat['detail'] }}</p>
                    </article>
                @endforeach
            </section>

            <section aria-labelledby="owner-todos-title" class="overflow-hidden rounded-2xl border border-warning-200 bg-white shadow-theme-xs dark:border-warning-500/20 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:px-6">
                    <div>
                        <h2 id="owner-todos-title" class="text-base font-semibold text-gray-900 dark:text-white">{{ __('To do now') }}</h2>
                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Illustrative actions that need an owner response.') }}</p>
                    </div>
                    <span class="rounded-full bg-warning-50 px-3 py-1 text-xs font-semibold text-warning-700 dark:bg-warning-500/10 dark:text-warning-300">{{ __('5 items') }}</span>
                </div>
                <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($ownerTodos as $todo)
                        <li class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div class="flex items-start gap-3">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-warning-50 text-sm font-semibold text-warning-700 dark:bg-warning-500/10 dark:text-warning-300">{{ $todo['count'] }}</span>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ $todo['title'] }}</h3>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $todo['detail'] }}</p>
                                </div>
                            </div>
                            <a href="{{ route($todo['route']) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-gray-200 px-4 text-sm font-medium text-gray-700 hover:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5">{{ $todo['action'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section aria-labelledby="owner-quick-access-title" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-4 flex flex-wrap items-end justify-between gap-2">
                    <div>
                        <h2 id="owner-quick-access-title" class="font-semibold text-gray-900 dark:text-white">{{ __('Quick access') }}</h2>
                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Jump straight into the work that keeps your listings moving.') }}</p>
                    </div>
                    <span class="text-theme-xs font-medium text-gray-400">{{ __('OWNER STUDIO') }}</span>
                </div>
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ([
                        ['title' => __('Equipment'), 'detail' => __('Listings & energy profiles'), 'icon' => 'leaf', 'route' => 'front.my-equipment', 'tone' => 'brand'],
                        ['title' => __('Availability'), 'detail' => __('Calendar & service dates'), 'icon' => 'calendar', 'route' => 'front.my-calendar', 'tone' => 'warning'],
                        ['title' => __('Reservations'), 'detail' => __('Requests from renters'), 'icon' => 'document', 'route' => 'front.my-reservations', 'tone' => 'success'],
                        ['title' => __('Earnings'), 'detail' => __('Payments & invoices'), 'icon' => 'wallet', 'route' => 'front.my-earnings', 'tone' => 'brand'],
                    ] as $shortcut)
                        <a href="{{ route($shortcut['route']) }}" class="group flex min-h-20 items-center gap-3 rounded-xl border border-gray-200 p-3 transition-colors hover:border-brand-300 hover:bg-brand-50/60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:border-gray-800 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/5">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl {{ $shortcut['tone'] === 'warning' ? 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-300' : ($shortcut['tone'] === 'success' ? 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-300' : 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300') }}"><x-front.icon :name="$shortcut['icon']" class="size-5" /></span>
                            <span class="min-w-0 flex-1"><span class="block text-theme-xs font-semibold text-gray-900 dark:text-white">{{ $shortcut['title'] }}</span><span class="mt-1 block truncate text-[11px] text-gray-500 dark:text-gray-400">{{ $shortcut['detail'] }}</span></span>
                            <span class="text-brand-700 transition-transform group-hover:translate-x-0.5 rtl:rotate-180 dark:text-brand-300" aria-hidden="true">→</span>
                        </a>
                    @endforeach
                </div>
            </section>

            <section aria-labelledby="owner-analytics-title">
                <div class="mb-5">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p class="text-theme-xs font-semibold uppercase tracking-[0.16em] text-brand-700 dark:text-brand-300">{{ __('OWNER STUDIO · ANALYTICS') }}</p>
                            <h2 id="owner-analytics-title" class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ __('Your performance at a glance') }}</h2>
                            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ __('Four signals to help you plan the next equipment hand-off.') }}</p>
                        </div>
                        <a href="{{ route('front.my-earnings') }}" class="inline-flex min-h-10 items-center rounded-lg px-3 text-theme-xs font-semibold text-brand-700 hover:bg-brand-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-brand-300 dark:hover:bg-brand-500/10">{{ __('All earnings') }} <span class="ms-2" aria-hidden="true">→</span></a>
                    </div>
                </div>
                <div class="grid gap-5 xl:grid-cols-2">
                    @foreach ($ownerCharts as $chart)
                        <x-common.component-card :title="$chart['title']" :desc="$chart['description']">
                            <x-charts.apex :options="$chart['options']" :height="280" :label="$chart['title']" />
                        </x-common.component-card>
                    @endforeach
                </div>
            </section>

            <section aria-labelledby="owner-activity-title" class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:px-6">
                    <h2 id="owner-activity-title" class="font-semibold text-gray-900 dark:text-white">{{ __('Latest activity') }}</h2>
                    <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Example events from the reservation-to-return journey.') }}</p>
                </div>
                <ol class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($ownerActivity as $activity)
                        <li class="flex gap-4 px-5 py-4 sm:px-6">
                            <span class="mt-1.5 size-2.5 shrink-0 rounded-full {{ $activity['tone'] === 'warning' ? 'bg-warning-500' : ($activity['tone'] === 'success' ? 'bg-success-500' : 'bg-brand-500') }}" aria-hidden="true"></span>
                            <div class="min-w-0 flex-1 sm:flex sm:items-start sm:justify-between sm:gap-5">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ $activity['title'] }}</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $activity['detail'] }}</p>
                                </div>
                                <time class="mt-1 block shrink-0 text-xs text-gray-400 sm:mt-0">{{ $activity['time'] }}</time>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>

        @else
        <div class="flex items-start gap-3 rounded-2xl border border-warning-200 bg-warning-50 px-4 py-4 text-theme-sm leading-6 text-warning-800 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-200" role="status">
            <span class="mt-0.5 text-lg" aria-hidden="true">✳</span>
            <p><span class="font-semibold">{{ __('Interactive preview.') }}</span> {{ __('All activity and totals shown here are sample data, not connected to live bookings.') }}</p>
        </div>

        <section aria-label="{{ __('Buyer dashboard statistics') }}" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($stats as $stat)
                <article class="animate-energy-enter relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs transition duration-300 hover:-translate-y-1 hover:shadow-theme-md motion-reduce:transition-none motion-reduce:hover:translate-y-0 dark:border-gray-800 dark:bg-white/[0.03]">
                    <span class="absolute inset-x-0 top-0 h-1 bg-linear-to-r from-brand-500 to-warning-300" aria-hidden="true"></span>
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-theme-sm font-medium text-gray-500 dark:text-gray-400">{{ $stat['label'] }}</p>
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl {{ $tones[$stat['tone']] }}" aria-hidden="true"><x-front.icon :name="$stat['icon']" class="size-5" /></span>
                    </div>
                    <p class="mt-5 text-title-sm font-semibold tabular-nums text-gray-900 dark:text-white">{{ $stat['value'] }}</p>
                    <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Your clean-energy snapshot') }}</p>
                </article>
            @endforeach
        </section>

        <section aria-label="{{ __('Next reservation and reminders') }}" class="grid gap-5 lg:grid-cols-[1.2fr_0.8fr]">
            <article class="relative overflow-hidden rounded-3xl border border-brand-200 bg-linear-to-br from-brand-50 via-white to-warning-50 p-6 shadow-theme-xs dark:border-brand-500/20 dark:from-brand-950/60 dark:via-gray-900 dark:to-warning-950/30">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-theme-xs font-semibold uppercase tracking-[0.16em] text-brand-700 dark:text-brand-300">{{ __('Up next · RES-1042') }}</p>
                        <h2 class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">{{ __('Portable battery 1000 Wh') }}</h2>
                        <p class="mt-2 text-theme-sm text-gray-600 dark:text-gray-300">{{ __('9–11 Oct 2026 · La Marsa, Tunis · 18 TND / day') }}</p>
                    </div>
                    <span class="rounded-full bg-success-50 px-3 py-1.5 text-theme-xs font-semibold text-success-700 dark:bg-success-500/15 dark:text-success-300">{{ __('Confirmed') }}</span>
                </div>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('front.buyer-reservation-detail') }}" class="button-base button-primary min-h-10 px-4 text-theme-xs">{{ __('View reservation') }}</a>
                    <a href="{{ route('front.my-rentals') }}" class="inline-flex min-h-10 items-center rounded-lg border border-gray-300 px-4 text-theme-xs font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">{{ __('My rentals') }}</a>
                </div>
            </article>
            <aside class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="font-semibold text-gray-900 dark:text-white">{{ __('To do now') }}</h2>
                <ul class="mt-4 space-y-4 text-theme-xs">
                    <li class="flex items-start gap-3"><span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-warning-50 font-semibold text-warning-800 dark:bg-warning-500/15 dark:text-warning-300">1</span><span class="pt-1 text-gray-700 dark:text-gray-300">{{ __('Check the owner response to your pending reservation.') }}<a href="{{ route('front.my-reservations') }}" class="ms-1 font-semibold text-brand-700 dark:text-brand-300">{{ __('Open') }}</a></span></li>
                    <li class="flex items-start gap-3"><span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-brand-50 font-semibold text-brand-800 dark:bg-brand-500/15 dark:text-brand-300">!</span><span class="pt-1 text-gray-700 dark:text-gray-300">{{ __('RNT-2031 return is coming up. Review your contract and return date.') }}<a href="{{ route('front.my-rentals') }}" class="ms-1 font-semibold text-brand-700 dark:text-brand-300">{{ __('Open') }}</a></span></li>
                </ul>
            </aside>
        </section>

        <section aria-labelledby="buyer-analytics-title">
            <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div><p class="text-theme-xs font-semibold uppercase tracking-[0.16em] text-brand-700 dark:text-brand-300">{{ __('YOUR ACTIVITY · ANALYTICS') }}</p><h2 id="buyer-analytics-title" class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ __('Your rental snapshot') }}</h2><p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ __('Sample spending, category and booking trends.') }}</p></div>
                <a href="{{ route('front.my-payments') }}" class="inline-flex min-h-10 items-center rounded-lg px-3 text-theme-xs font-semibold text-brand-700 hover:bg-brand-50 dark:text-brand-300 dark:hover:bg-brand-500/10">{{ __('Payments & invoices') }} <span class="ms-2" aria-hidden="true">→</span></a>
            </div>
            <div class="grid gap-5 xl:grid-cols-2">
                @foreach ($buyerCharts as $chart)
                    <x-common.component-card :title="$chart['title']" :desc="$chart['description']">
                        <x-charts.apex :options="$chart['options']" :height="270" :label="$chart['title']" />
                    </x-common.component-card>
                @endforeach
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-[1.35fr_0.65fr]">
            <section aria-labelledby="activity-title" class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-5 py-5 dark:border-gray-800 sm:px-6">
                    <div>
                        <h2 id="activity-title" class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Your rental activity') }}</h2>
                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('A sample of what matters right now') }}</p>
                    </div>
                    <a href="{{ route('front.my-reservations') }}" class="inline-flex min-h-11 items-center rounded-lg px-3 text-theme-sm font-medium text-brand-700 hover:bg-brand-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-brand-300 dark:hover:bg-brand-500/10">
                        {{ __('View reservations') }} <span class="ms-1" aria-hidden="true">→</span>
                    </a>
                </div>
                <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($activities as $activity)
                        <li class="flex flex-col gap-3 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="mt-1 size-2 shrink-0 rounded-full bg-brand-500" aria-hidden="true"></span>
                                <div class="min-w-0">
                                    <h3 class="font-medium text-gray-800 dark:text-white/90">{{ $activity['title'] }}</h3>
                                    <p class="mt-1 break-words text-theme-sm text-gray-500 dark:text-gray-400">{{ $activity['detail'] }}</p>
                                </div>
                            </div>
                            <span class="ms-5 inline-flex w-fit shrink-0 rounded-full px-3 py-1 text-theme-xs font-medium sm:ms-0 {{ $tones[$activity['tone']] }}">{{ $activity['status'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>

            <aside class="rounded-2xl border border-brand-200 bg-linear-to-br from-brand-50 via-white to-warning-50 p-6 dark:border-brand-500/20 dark:from-brand-950/60 dark:via-gray-900 dark:to-warning-950/30">
                <span class="flex size-12 items-center justify-center rounded-2xl bg-brand-950 text-brand-200 shadow-theme-xs dark:bg-brand-500/15" aria-hidden="true"><x-front.icon name="handshake" /></span>
                <h2 class="mt-5 text-lg font-semibold text-gray-900 dark:text-white">{{ __('Small choices add up.') }}</h2>
                <p class="mt-2 text-theme-sm leading-6 text-gray-600 dark:text-gray-300">
                    {{ __('Rent the gear you need for a few days, then pass it on for someone else to use.') }}
                </p>
                <a href="{{ route('front.catalog') }}" class="button-base button-primary mt-6 w-full px-4 text-theme-sm">{{ __('Explore the catalog') }}</a>
            </aside>
        </div>
        @endif
    </div>
@endsection
