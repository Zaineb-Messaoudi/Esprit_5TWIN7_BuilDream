@extends('layouts.app')

@section('content')
    @php
        $metrics = [
            ['label' => 'Generations', 'value' => '12,840', 'change' => '+18.4%'],
            ['label' => 'Tokens used', 'value' => '8.42M', 'change' => '+6.2%'],
            ['label' => 'Average latency', 'value' => '1.24s', 'change' => '-0.18s'],
            ['label' => 'Success rate', 'value' => '99.2%', 'change' => '+0.4%'],
        ];
        $models = [
            ['name' => 'SolarShare Assistant', 'type' => 'Text generation · v2.4', 'requests' => '6,420', 'usage' => 78, 'status' => 'Operational'],
            ['name' => 'Product Description Writer', 'type' => 'Content generation · v1.8', 'requests' => '3,840', 'usage' => 52, 'status' => 'Operational'],
            ['name' => 'Support Classifier', 'type' => 'Classification · v1.2', 'requests' => '2,580', 'usage' => 34, 'status' => 'Operational'],
        ];
        $activities = [
            ['title' => 'Product copy generated', 'detail' => 'Solar panel 420W · 248 tokens', 'time' => '4 min ago', 'color' => 'bg-brand-500'],
            ['title' => 'Support reply drafted', 'detail' => 'Ticket #SS-2841 · 186 tokens', 'time' => '18 min ago', 'color' => 'bg-blue-light-500'],
            ['title' => 'Lead intent classified', 'detail' => 'Residential inquiry · high intent', 'time' => '32 min ago', 'color' => 'bg-success-500'],
            ['title' => 'Usage report summarized', 'detail' => 'September weekly report · 412 tokens', 'time' => '1 hour ago', 'color' => 'bg-warning-500'],
        ];
    @endphp

    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Dashboard / AI</p>
                <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">AI operations overview</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Illustrative usage dashboard only; no AI provider or backend is connected.</p>
            </div>
            <label class="sr-only" for="ai-period">Usage period</label>
            <select id="ai-period" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"><option>Last 30 days</option><option>Last 7 days</option><option>This year</option></select>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:gap-6">
            @foreach ($metrics as $metric)
                <x-common.component-card :title="$metric['label']">
                    <p class="text-title-md font-semibold text-gray-800 dark:text-white/90">{{ $metric['value'] }}</p>
                    <p class="mt-2 text-xs text-success-600">{{ $metric['change'] }} <span class="text-gray-400">vs previous period</span></p>
                </x-common.component-card>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <x-common.component-card title="Generation volume" desc="Requests by day · sample usage data" class="xl:col-span-2">
                <div class="mb-4 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span>12,840 total requests</span>
                    <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-brand-500"></span>Successful generations</span>
                </div>
                <div class="grid h-52 grid-cols-12 items-end gap-2 border-b border-gray-100 pb-5 sm:gap-4 dark:border-gray-800">
                    @foreach ([42, 58, 49, 72, 63, 81, 54, 68, 88, 70, 92, 78] as $index => $value)
                        <div class="flex h-full flex-col items-center justify-end gap-2">
                            <div class="w-full rounded-t bg-brand-500/85 hover:bg-brand-600" style="height: {{ $value }}%" title="Day {{ $index + 1 }}: {{ $value }}% of peak requests"></div>
                            @if (in_array($index, [0, 2, 4, 6, 8, 10], true))
                                <span class="text-[10px] text-gray-400">{{ $index + 1 }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div><p class="text-xs text-gray-400">Input tokens</p><p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white">5.18M</p></div>
                    <div><p class="text-xs text-gray-400">Output tokens</p><p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white">3.24M</p></div>
                    <div><p class="text-xs text-gray-400">Avg. tokens / request</p><p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white">656</p></div>
                    <div><p class="text-xs text-gray-400">Estimated cost</p><p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white">$42.80</p></div>
                </div>
            </x-common.component-card>

            <x-common.component-card title="AI activity" desc="Recent sample generations">
                <div class="space-y-5">
                    @foreach ($activities as $activity)
                        <div class="flex gap-3">
                            <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $activity['color'] }}"></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $activity['title'] }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $activity['detail'] }}</p>
                                <p class="mt-1 text-[11px] text-gray-400">{{ $activity['time'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-common.component-card>
        </div>

        <x-common.component-card title="Models" desc="Sample model usage and operational status">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[680px] text-start text-sm">
                    <thead><tr class="border-b border-gray-100 text-xs text-gray-400 dark:border-gray-800"><th class="px-3 py-3 font-medium">Model</th><th class="px-3 py-3 font-medium">Status</th><th class="px-3 py-3 text-end font-medium">Requests</th><th class="px-3 py-3 font-medium">Usage</th></tr></thead>
                    <tbody>
                        @foreach ($models as $model)
                            <tr class="border-b border-gray-50 last:border-0 dark:border-gray-800">
                                <td class="px-3 py-4"><p class="font-medium text-gray-800 dark:text-white/90">{{ $model['name'] }}</p><p class="mt-1 text-xs text-gray-400">{{ $model['type'] }}</p></td>
                                <td class="px-3 py-4"><x-ui.badge variant="light" color="success">{{ $model['status'] }}</x-ui.badge></td>
                                <td class="px-3 py-4 text-end text-gray-600 dark:text-gray-400">{{ $model['requests'] }}</td>
                                <td class="px-3 py-4">
                                    <div class="flex items-center gap-3"><div class="h-2 min-w-20 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800"><div class="h-full rounded-full bg-brand-500" style="width: {{ $model['usage'] }}%"></div></div><span class="w-9 text-end text-xs text-gray-500">{{ $model['usage'] }}%</span></div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-common.component-card>
    </div>
@endsection
