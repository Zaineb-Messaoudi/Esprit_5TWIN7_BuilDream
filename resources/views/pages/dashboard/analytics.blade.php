@extends('layouts.app')

@section('content')
    @php
        $metrics = [
            ['label' => 'Unique visitors', 'value' => '124,580', 'change' => '+12.8%', 'color' => 'success'],
            ['label' => 'Page views', 'value' => '482,910', 'change' => '+8.2%', 'color' => 'success'],
            ['label' => 'Bounce rate', 'value' => '42.3%', 'change' => '-2.4%', 'color' => 'success'],
            ['label' => 'Avg. session duration', 'value' => '3m 42s', 'change' => '+5.1%', 'color' => 'success'],
        ];
        $sources = [
            ['name' => 'Organic search', 'visitors' => '48,210', 'share' => 68, 'color' => 'bg-brand-500'],
            ['name' => 'Direct', 'visitors' => '32,640', 'share' => 46, 'color' => 'bg-blue-light-500'],
            ['name' => 'Social media', 'visitors' => '21,380', 'share' => 31, 'color' => 'bg-success-500'],
            ['name' => 'Referral', 'visitors' => '12,440', 'share' => 18, 'color' => 'bg-warning-500'],
        ];
        $pages = [
            ['path' => '/products/solar-panel-420w', 'views' => '28,420', 'change' => '+14.2%'],
            ['path' => '/solutions/residential', 'views' => '19,840', 'change' => '+8.6%'],
            ['path' => '/guides/solar-installation', 'views' => '15,290', 'change' => '+5.1%'],
            ['path' => '/products/home-battery', 'views' => '12,680', 'change' => '-1.3%'],
        ];
        $trend = '0,122 52,110 104,116 156,88 208,96 260,68 312,78 364,54 416,63 468,37 520,48 572,20';
    @endphp

    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Dashboard / Analytics</p>
                <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">Analytics overview</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Website traffic and engagement · illustrative data for the last 30 days</p>
            </div>
            <label class="sr-only" for="analytics-period">Reporting period</label>
            <select id="analytics-period" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option>Last 30 days</option><option>Last 7 days</option><option>This year</option>
            </select>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:gap-6">
            @foreach ($metrics as $metric)
                <x-common.component-card :title="$metric['label']">
                    <div class="flex items-end justify-between gap-2">
                        <p class="text-title-md font-semibold text-gray-800 dark:text-white/90">{{ $metric['value'] }}</p>
                        <x-ui.badge variant="light" :color="$metric['color']">{{ $metric['change'] }}</x-ui.badge>
                    </div>
                    <p class="mt-2 text-xs text-gray-400">Compared with previous period</p>
                </x-common.component-card>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <x-common.component-card title="Visitor analytics" desc="Daily sessions and unique visitors" class="xl:col-span-2">
                <div class="mb-5 flex flex-wrap gap-4 text-xs text-gray-500 dark:text-gray-400">
                    <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-brand-500"></span>Sessions</span>
                    <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-blue-light-500"></span>Unique visitors</span>
                    <span class="ms-auto font-medium text-gray-700 dark:text-gray-300">18,640 active users</span>
                </div>
                <svg viewBox="0 0 600 160" class="h-52 w-full" role="img" aria-label="Daily website sessions trend">
                    <title>Daily website sessions trend</title>
                    @foreach ([30, 70, 110, 150] as $y)
                        <line x1="0" y1="{{ $y }}" x2="600" y2="{{ $y }}" stroke="currentColor" class="text-gray-100 dark:text-gray-800" stroke-dasharray="4 5" />
                    @endforeach
                    <polyline points="{{ $trend }}" fill="none" stroke="currentColor" class="text-brand-500" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                    <polyline points="0,140 52,126 104,132 156,106 208,112 260,92 312,102 364,78 416,88 468,62 520,74 572,48" fill="none" stroke="currentColor" class="text-blue-light-500" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="mt-2 flex justify-between text-xs text-gray-400"><span>Sep 1</span><span>Sep 7</span><span>Sep 14</span><span>Sep 21</span><span>Sep 30</span></div>
            </x-common.component-card>

            <x-common.component-card title="Acquisition channels" desc="Sessions by traffic source">
                <div class="space-y-5">
                    @foreach ($sources as $source)
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-2 text-sm">
                                <span class="truncate font-medium text-gray-700 dark:text-gray-300">{{ $source['name'] }}</span>
                                <span class="shrink-0 text-gray-500 dark:text-gray-400">{{ $source['visitors'] }}</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                <div class="h-full rounded-full {{ $source['color'] }}" style="width: {{ $source['share'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="mt-5 border-t border-gray-100 pt-4 text-xs text-gray-400 dark:border-gray-800">Sessions attributed using the selected reporting period.</p>
            </x-common.component-card>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-5">
            <x-common.component-card title="Top pages" desc="Most visited pages during this period" class="xl:col-span-3">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[520px] text-start text-sm">
                        <thead><tr class="border-b border-gray-100 text-xs text-gray-400 dark:border-gray-800"><th class="px-3 py-3 font-medium">Page</th><th class="px-3 py-3 text-end font-medium">Views</th><th class="px-3 py-3 text-end font-medium">Change</th></tr></thead>
                        <tbody>
                            @foreach ($pages as $page)
                                <tr class="border-b border-gray-50 last:border-0 dark:border-gray-800">
                                    <td class="max-w-[260px] truncate px-3 py-4 font-medium text-gray-700 dark:text-gray-300">{{ $page['path'] }}</td>
                                    <td class="px-3 py-4 text-end text-gray-600 dark:text-gray-400">{{ $page['views'] }}</td>
                                    <td class="px-3 py-4 text-end {{ str_starts_with($page['change'], '-') ? 'text-error-500' : 'text-success-600' }}">{{ $page['change'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-common.component-card>

            <x-common.component-card title="Audience devices" desc="Share of active sessions" class="xl:col-span-2">
                <div class="space-y-5">
                    @foreach ([['Desktop', '56%', 56], ['Mobile', '37%', 37], ['Tablet', '7%', 7]] as [$device, $share, $width])
                        <div class="flex items-center gap-3">
                            <span class="w-16 text-sm text-gray-600 dark:text-gray-400">{{ $device }}</span>
                            <div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800"><div class="h-full rounded-full bg-brand-500" style="width: {{ $width }}%"></div></div>
                            <span class="w-10 text-end text-sm font-medium text-gray-700 dark:text-gray-300">{{ $share }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6 rounded-lg bg-gray-50 p-4 dark:bg-gray-800/50">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Top region</p>
                    <p class="mt-1 font-semibold text-gray-800 dark:text-white/90">Tunis, Tunisia <span class="text-sm font-normal text-gray-500">· 34.8%</span></p>
                </div>
            </x-common.component-card>
        </div>
    </div>
@endsection
