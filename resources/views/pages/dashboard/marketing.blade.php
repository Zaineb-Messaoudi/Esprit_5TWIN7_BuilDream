@extends('layouts.app')

@section('content')
    @php
        $metrics = [
            ['label' => 'Campaign spend', 'value' => '$18,460', 'change' => '8.4% under budget', 'color' => 'success'],
            ['label' => 'Attributed revenue', 'value' => '$64,280', 'change' => '+16.2%', 'color' => 'success'],
            ['label' => 'New leads', 'value' => '2,486', 'change' => '+11.8%', 'color' => 'success'],
            ['label' => 'Return on ad spend', 'value' => '3.48x', 'change' => '+0.34x', 'color' => 'success'],
        ];
        $campaigns = [
            ['name' => 'Autumn Solar Upgrade', 'channel' => 'Search · 12 days left', 'status' => 'Active', 'spend' => '$4,820', 'revenue' => '$18,640', 'return' => '3.86x', 'color' => 'success'],
            ['name' => 'Home Battery Awareness', 'channel' => 'Social · 18 days left', 'status' => 'Active', 'spend' => '$3,240', 'revenue' => '$10,880', 'return' => '3.36x', 'color' => 'success'],
            ['name' => 'Installer Partner Webinar', 'channel' => 'Email · 4 days left', 'status' => 'Scheduled', 'spend' => '$1,160', 'revenue' => '$2,840', 'return' => '2.45x', 'color' => 'warning'],
            ['name' => 'Summer Clearance', 'channel' => 'Search · Completed', 'status' => 'Completed', 'spend' => '$5,600', 'revenue' => '$19,320', 'return' => '3.45x', 'color' => 'gray'],
        ];
        $channels = [
            ['name' => 'Paid search', 'leads' => '842', 'share' => 78, 'color' => 'bg-brand-500'],
            ['name' => 'Social media', 'leads' => '618', 'share' => 59, 'color' => 'bg-blue-light-500'],
            ['name' => 'Email marketing', 'leads' => '504', 'share' => 48, 'color' => 'bg-success-500'],
            ['name' => 'Partner referrals', 'leads' => '322', 'share' => 31, 'color' => 'bg-warning-500'],
        ];
    @endphp

    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Dashboard / Marketing</p>
                <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">Marketing overview</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Campaign performance and lead acquisition · demo data</p>
            </div>
            <x-ui.button variant="primary">Create campaign</x-ui.button>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:gap-6">
            @foreach ($metrics as $metric)
                <x-common.component-card :title="$metric['label']">
                    <p class="text-title-md font-semibold text-gray-800 dark:text-white/90">{{ $metric['value'] }}</p>
                    <p class="mt-2 text-xs text-{{ $metric['color'] === 'success' ? 'success-600' : 'gray-500' }}">{{ $metric['change'] }}</p>
                </x-common.component-card>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <x-common.component-card title="Campaign performance" desc="Spend and attributed revenue this quarter" class="xl:col-span-2">
                <div class="mb-4 flex justify-end gap-4 text-xs text-gray-500 dark:text-gray-400">
                    <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-brand-500"></span>Revenue</span>
                    <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-blue-light-500"></span>Spend</span>
                </div>
                <div class="grid h-52 grid-cols-6 items-end gap-4 border-b border-gray-100 pb-5 dark:border-gray-800">
                    @foreach ([['Jan', 62, 38], ['Feb', 74, 42], ['Mar', 56, 34], ['Apr', 86, 49], ['May', 72, 40], ['Jun', 94, 55]] as [$month, $revenue, $spend])
                        <div class="flex h-full flex-col items-center justify-end gap-2">
                            <div class="flex h-full w-full items-end justify-center gap-1">
                                <div class="w-1/3 rounded-t bg-brand-500" style="height: {{ $revenue }}%" title="{{ $month }} revenue"></div>
                                <div class="w-1/3 rounded-t bg-blue-light-500" style="height: {{ $spend }}%" title="{{ $month }} spend"></div>
                            </div>
                            <span class="text-xs text-gray-400">{{ $month }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 flex flex-wrap justify-between gap-3 text-sm">
                    <p class="text-gray-500 dark:text-gray-400">Attributed conversions <span class="ms-1 font-semibold text-gray-800 dark:text-white">1,842</span></p>
                    <p class="text-gray-500 dark:text-gray-400">Cost per lead <span class="ms-1 font-semibold text-gray-800 dark:text-white">$7.42</span></p>
                </div>
            </x-common.component-card>

            <x-common.component-card title="Acquisition channels" desc="Qualified leads by source">
                <div class="space-y-5">
                    @foreach ($channels as $channel)
                        <div>
                            <div class="mb-2 flex items-center justify-between text-sm">
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $channel['name'] }}</span>
                                <span class="text-gray-500 dark:text-gray-400">{{ $channel['leads'] }}</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                <div class="h-full rounded-full {{ $channel['color'] }}" style="width: {{ $channel['share'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="mt-5 text-xs text-gray-400">2,286 leads assigned to a marketing source.</p>
            </x-common.component-card>
        </div>

        <x-common.component-card title="Campaigns" desc="Status and return for recent campaigns">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-start text-sm">
                    <thead><tr class="border-b border-gray-100 text-xs text-gray-400 dark:border-gray-800"><th class="px-3 py-3 font-medium">Campaign</th><th class="px-3 py-3 font-medium">Status</th><th class="px-3 py-3 text-end font-medium">Spend</th><th class="px-3 py-3 text-end font-medium">Revenue</th><th class="px-3 py-3 text-end font-medium">ROAS</th></tr></thead>
                    <tbody>
                        @foreach ($campaigns as $campaign)
                            <tr class="border-b border-gray-50 last:border-0 dark:border-gray-800">
                                <td class="px-3 py-4"><p class="font-medium text-gray-800 dark:text-white/90">{{ $campaign['name'] }}</p><p class="mt-1 text-xs text-gray-400">{{ $campaign['channel'] }}</p></td>
                                <td class="px-3 py-4"><x-ui.badge variant="light" :color="$campaign['color']">{{ $campaign['status'] }}</x-ui.badge></td>
                                <td class="px-3 py-4 text-end text-gray-600 dark:text-gray-400">{{ $campaign['spend'] }}</td>
                                <td class="px-3 py-4 text-end text-gray-600 dark:text-gray-400">{{ $campaign['revenue'] }}</td>
                                <td class="px-3 py-4 text-end font-medium text-gray-800 dark:text-white">{{ $campaign['return'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-common.component-card>
    </div>
@endsection
