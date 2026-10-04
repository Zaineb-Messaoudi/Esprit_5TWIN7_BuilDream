@extends('layouts.app')

@section('content')
    @php
        $metrics = [
            ['label' => 'Monthly recurring revenue', 'value' => '$84,260', 'change' => '+12.8%'],
            ['label' => 'Annual recurring revenue', 'value' => '$1.01M', 'change' => '+14.2%'],
            ['label' => 'Active subscribers', 'value' => '2,486', 'change' => '+8.6%'],
            ['label' => 'Monthly churn', 'value' => '2.4%', 'change' => '-0.3%'],
        ];
        $plans = [
            ['name' => 'Starter', 'subscribers' => 1248, 'mrr' => '$24,960', 'share' => 72, 'color' => 'bg-brand-500'],
            ['name' => 'Professional', 'subscribers' => 864, 'mrr' => '$43,200', 'share' => 54, 'color' => 'bg-blue-light-500'],
            ['name' => 'Business', 'subscribers' => 374, 'mrr' => '$16,100', 'share' => 32, 'color' => 'bg-success-500'],
        ];
        $growth = '0,125 52,118 104,102 156,109 208,87 260,92 312,70 364,75 416,54 468,58 520,35 572,22';
    @endphp

    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Dashboard / SaaS</p>
                <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">Subscription overview</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Recurring revenue and subscriber health · illustrative data</p>
            </div>
            <label class="sr-only" for="saas-period">Reporting period</label>
            <select id="saas-period" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"><option>Last 12 months</option><option>Last 30 days</option></select>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:gap-6">
            @foreach ($metrics as $metric)
                <x-common.component-card :title="$metric['label']">
                    <p class="text-title-md font-semibold text-gray-800 dark:text-white/90">{{ $metric['value'] }}</p>
                    <p class="mt-2 text-xs text-success-600">{{ $metric['change'] }} <span class="text-gray-400">vs last month</span></p>
                </x-common.component-card>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <x-common.component-card title="Recurring revenue" desc="Monthly recurring revenue trend" class="xl:col-span-2">
                <div class="flex flex-wrap justify-between gap-3 text-sm">
                    <div><p class="text-xs text-gray-400">Current MRR</p><p class="mt-1 text-xl font-semibold text-gray-800 dark:text-white">$84,260</p></div>
                    <div class="text-end"><p class="text-xs text-gray-400">New MRR this month</p><p class="mt-1 text-xl font-semibold text-success-600">+$8,420</p></div>
                </div>
                <svg viewBox="0 0 600 160" class="mt-6 h-52 w-full" role="img" aria-label="Monthly recurring revenue trend">
                    <title>Monthly recurring revenue trend</title>
                    @foreach ([30, 70, 110, 150] as $y)
                        <line x1="0" y1="{{ $y }}" x2="600" y2="{{ $y }}" stroke="currentColor" class="text-gray-100 dark:text-gray-800" stroke-dasharray="4 5" />
                    @endforeach
                    <polygon points="0,160 {{ $growth }} 572,160" fill="currentColor" class="text-brand-500/10" />
                    <polyline points="{{ $growth }}" fill="none" stroke="currentColor" class="text-brand-500" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="flex justify-between text-xs text-gray-400"><span>Oct</span><span>Dec</span><span>Feb</span><span>Apr</span><span>Jun</span><span>Sep</span></div>
            </x-common.component-card>

            <x-common.component-card title="Plan distribution" desc="Subscribers and monthly revenue">
                <div class="space-y-6">
                    @foreach ($plans as $plan)
                        <div>
                            <div class="mb-2 flex items-start justify-between gap-2">
                                <div><p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $plan['name'] }}</p><p class="mt-1 text-xs text-gray-400">{{ number_format($plan['subscribers']) }} subscribers</p></div>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $plan['mrr'] }}</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800"><div class="h-full rounded-full {{ $plan['color'] }}" style="width: {{ $plan['share'] }}%"></div></div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6 grid grid-cols-2 gap-3 border-t border-gray-100 pt-4 dark:border-gray-800">
                    <div><p class="text-xs text-gray-400">Active trials</p><p class="mt-1 font-semibold text-gray-800 dark:text-white">184</p></div>
                    <div><p class="text-xs text-gray-400">Trial conversion</p><p class="mt-1 font-semibold text-gray-800 dark:text-white">18.6%</p></div>
                </div>
            </x-common.component-card>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <x-common.component-card title="Customer growth">
                <p class="text-2xl font-semibold text-gray-800 dark:text-white">+186</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Net subscribers this month</p>
            </x-common.component-card>
            <x-common.component-card title="Retention rate">
                <p class="text-2xl font-semibold text-gray-800 dark:text-white">97.6%</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Monthly subscriber retention</p>
            </x-common.component-card>
            <x-common.component-card title="Expansion MRR">
                <p class="text-2xl font-semibold text-gray-800 dark:text-white">$4,820</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Upgrade and add-on revenue</p>
            </x-common.component-card>
        </div>
    </div>
@endsection
