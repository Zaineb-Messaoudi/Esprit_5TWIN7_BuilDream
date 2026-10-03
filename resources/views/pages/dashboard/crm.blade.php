@extends('layouts.app')

@section('content')
    @php
        $metrics = [
            ['label' => 'Open pipeline', 'value' => '$284,600', 'change' => '+12.4%'],
            ['label' => 'Qualified leads', 'value' => '186', 'change' => '+18 this week'],
            ['label' => 'Deals won', 'value' => '42', 'change' => '+8.6%'],
            ['label' => 'Win rate', 'value' => '28.4%', 'change' => '+2.1%'],
        ];
        $stages = [
            ['name' => 'New leads', 'count' => 48, 'value' => '$86,400', 'share' => 88],
            ['name' => 'Qualified', 'count' => 32, 'value' => '$72,800', 'share' => 68],
            ['name' => 'Proposal', 'count' => 21, 'value' => '$58,200', 'share' => 48],
            ['name' => 'Negotiation', 'count' => 12, 'value' => '$42,600', 'share' => 30],
            ['name' => 'Closed won', 'count' => 8, 'value' => '$24,600', 'share' => 18],
        ];
        $leads = [
            ['company' => 'Atlas Energy Group', 'contact' => 'Sami Gharbi', 'value' => '$28,400', 'stage' => 'Negotiation', 'color' => 'primary', 'owner' => 'AB'],
            ['company' => 'Carthage Properties', 'contact' => 'Leila Mansour', 'value' => '$18,600', 'stage' => 'Proposal', 'color' => 'warning', 'owner' => 'YT'],
            ['company' => 'Green Horizon Ltd.', 'contact' => 'Nour Ben Ali', 'value' => '$42,800', 'stage' => 'Qualified', 'color' => 'success', 'owner' => 'MH'],
            ['company' => 'Medina Hospitality', 'contact' => 'Omar Trabelsi', 'value' => '$12,400', 'stage' => 'New lead', 'color' => 'gray', 'owner' => 'KM'],
        ];
        $team = [['name' => 'Amira Ben Salem', 'deals' => 14, 'revenue' => '$84,200'], ['name' => 'Youssef Trabelsi', 'deals' => 11, 'revenue' => '$72,800'], ['name' => 'Maya Haddad', 'deals' => 9, 'revenue' => '$58,600']];
    @endphp

    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Dashboard / CRM</p>
                <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">Sales pipeline</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Lead and opportunity overview · sample CRM data</p>
            </div>
            <x-ui.button variant="primary">Add new lead</x-ui.button>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:gap-6">
            @foreach ($metrics as $metric)
                <x-common.component-card :title="$metric['label']">
                    <p class="text-title-md font-semibold text-gray-800 dark:text-white/90">{{ $metric['value'] }}</p>
                    <p class="mt-2 text-xs text-success-600">{{ $metric['change'] }} <span class="text-gray-400">vs previous period</span></p>
                </x-common.component-card>
            @endforeach
        </div>

        <x-common.component-card title="Opportunity pipeline" desc="Active deals by stage">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
                @foreach ($stages as $stage)
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $stage['name'] }}</p>
                        <p class="mt-3 text-2xl font-semibold text-gray-800 dark:text-white">{{ $stage['count'] }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $stage['value'] }} potential</p>
                        <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800"><div class="h-full rounded-full bg-brand-500" style="width: {{ $stage['share'] }}%"></div></div>
                    </div>
                @endforeach
            </div>
        </x-common.component-card>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <x-common.component-card title="Recent opportunities" desc="Latest lead activity" class="xl:col-span-2">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[660px] text-start text-sm">
                        <thead><tr class="border-b border-gray-100 text-xs text-gray-400 dark:border-gray-800"><th class="px-3 py-3 font-medium">Company / contact</th><th class="px-3 py-3 font-medium">Stage</th><th class="px-3 py-3 text-end font-medium">Value</th><th class="px-3 py-3 text-end font-medium">Owner</th></tr></thead>
                        <tbody>
                            @foreach ($leads as $lead)
                                <tr class="border-b border-gray-50 last:border-0 dark:border-gray-800">
                                    <td class="px-3 py-4"><p class="font-medium text-gray-800 dark:text-white/90">{{ $lead['company'] }}</p><p class="mt-1 text-xs text-gray-400">{{ $lead['contact'] }}</p></td>
                                    <td class="px-3 py-4"><x-ui.badge variant="light" :color="$lead['color']">{{ $lead['stage'] }}</x-ui.badge></td>
                                    <td class="px-3 py-4 text-end font-medium text-gray-700 dark:text-gray-300">{{ $lead['value'] }}</td>
                                    <td class="px-3 py-4 text-end"><span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand-50 text-xs font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-300">{{ $lead['owner'] }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-common.component-card>

            <x-common.component-card title="Team performance" desc="Top representatives this month">
                <div class="space-y-5">
                    @foreach ($team as $index => $representative)
                        <div class="flex items-center gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ $index + 1 }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-800 dark:text-white/90">{{ $representative['name'] }}</p>
                                <p class="mt-1 text-xs text-gray-400">{{ $representative['deals'] }} deals won</p>
                            </div>
                            <span class="shrink-0 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $representative['revenue'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-5 rounded-lg bg-brand-50 p-4 dark:bg-brand-500/10">
                    <p class="text-xs text-brand-700 dark:text-brand-300">Recent activity</p>
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">Maya moved Green Horizon Ltd. to the qualified stage.</p>
                    <p class="mt-2 text-xs text-gray-400">24 minutes ago</p>
                </div>
            </x-common.component-card>
        </div>
    </div>
@endsection
