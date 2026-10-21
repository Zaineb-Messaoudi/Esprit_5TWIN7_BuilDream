@extends('layouts.app')

@section('content')
<div class="grid grid-cols-12 gap-4 md:gap-6">

    <!-- Metric Cards Row -->
    <div class="col-span-12 xl:col-span-12">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6 xl:grid-cols-8">
            <x-ecommerce.metric-card
                :icon="'ecommerce'"
                :title="__('Total Equipment')"
                :value="$metrics['equipment']['total']"
                :subtext="__(':count available', ['count' => $metrics['equipment']['published']])"
            />
            <x-ecommerce.metric-card
                :icon="'ecommerce'"
                :title="__('Pending Review')"
                :value="$metrics['equipment']['pending_review']"
                :subtext="__(':count draft', ['count' => $metrics['equipment']['draft']])"
            />
            <x-ecommerce.metric-card
                :icon="'task'"
                :title="__('Active Rentals')"
                :value="$metrics['rentals']['active']"
                :subtext="__(':count completed', ['count' => $metrics['rentals']['completed']])"
                :trend="['value' => '+'.($metrics['rentals']['total'] > 0 ? round(($metrics['rentals']['completed']/$metrics['rentals']['total'])*100,1) : 0).'%', 'type' => 'up']"
            />
            <x-ecommerce.metric-card
                :icon="'task'"
                :title="__('Pending Reservations')"
                :value="$metrics['reservations']['pending']"
                :subtext="__(':count total', ['count' => $metrics['reservations']['total']])"
            />
            <x-ecommerce.metric-card
                :icon="'finance'"
                :title="__('Total Revenue')"
                :value="number_format($metrics['revenue']['total'], 2).' TND'"
                :subtext="__(':amount this month', ['amount' => number_format($metrics['revenue']['this_month'], 2).' TND'])"
                :trend="['value' => ($metrics['revenue']['growth'] >= 0 ? '+' : '').$metrics['revenue']['growth'].'%', 'type' => $metrics['revenue']['growth'] >= 0 ? 'up' : 'down']"
            />
            <x-ecommerce.metric-card
                :icon="'finance'"
                :title="__('Pending Revenue')"
                :value="number_format($metrics['revenue']['pending'], 2).' TND'"
                :subtext="__(':count overdue invoices', ['count' => $metrics['invoices']['overdue']])"
            />
            <x-ecommerce.metric-card
                :icon="'logistics'"
                :title="__('Open Maintenance')"
                :value="$metrics['maintenance']['open']"
                :subtext="__(':count in progress', ['count' => $metrics['maintenance']['in_progress']])"
            />
            <x-ecommerce.metric-card
                :icon="'user-profile'"
                :title="__('Active Users')"
                :value="$metrics['users']['total']"
                :subtext="__(':count owners / :count buyers', ['count' => $metrics['users']['owners'], 'count2' => $metrics['users']['buyers']])"
            />
        </div>
    </div>

    <!-- Charts Row -->
    <div class="col-span-12 xl:col-span-8">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Revenue Trend (12 Months)') }}</h3>
            <div class="h-64">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-span-12 xl:col-span-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] h-full">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Equipment by Category') }}</h3>
            <div class="h-64">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Status Distribution Charts -->
    <div class="col-span-12 xl:col-span-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Equipment Status') }}</h3>
            <div class="h-48">
                <canvas id="equipmentStatusChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-span-12 xl:col-span-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Reservations & Rentals Status') }}</h3>
            <div class="h-48">
                <canvas id="rentalStatusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Module Activity Tables -->
    <div class="col-span-12 xl:col-span-6">
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-800 px-5 py-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Recent Equipment') }}</h3>
                <a href="{{ route('admin.equipment.index') }}" class="text-sm text-brand-500 hover:underline">{{ __('View All') }}</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Name') }}</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Category') }}</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Status') }}</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Owner') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($recentEquipment as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                            <td class="px-5 py-4">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $item->name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $item->brand }} {{ $item->model }}</div>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $item->category->name ?? '—' }}</td>
                            <td class="px-5 py-4">
                                <x-ui.badge :variant="\App\Models\Equipment::STATUS_BADGE[$item->status] ?? 'default'" :size="'sm'">{{ ucfirst($item->status) }}</x-ui.badge>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $item->owner->name ?? '—' }}</td>
                        </tr>
                        @endforeach
                        @if($recentEquipment->isEmpty())
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">{{ __('No equipment found') }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-span-12 xl:col-span-6">
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-800 px-5 py-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Recent Reservations') }}</h3>
                <a href="{{ route('rental.reservations.index') }}" class="text-sm text-brand-500 hover:underline">{{ __('View All') }}</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Reference') }}</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Equipment') }}</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('User') }}</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($recentReservations as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                            <td class="px-5 py-4 font-medium text-gray-900 dark:text-white">{{ $item->reference }}</td>
                            <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $item->equipment->name ?? '—' }}</td>
                            <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $item->user->name ?? '—' }}</td>
                            <td class="px-5 py-4">
                                <x-ui.badge :variant="\App\Models\Reservation::STATUS_BADGE[$item->status] ?? 'default'" :size="'sm'">{{ ucfirst($item->status) }}</x-ui.badge>
                            </td>
                        </tr>
                        @endforeach
                        @if($recentReservations->isEmpty())
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">{{ __('No reservations found') }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-span-12 xl:col-span-6">
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-800 px-5 py-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Recent Rentals') }}</h3>
                <a href="{{ route('admin.rentals.index') }}" class="text-sm text-brand-500 hover:underline">{{ __('View All') }}</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Reference') }}</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Equipment') }}</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('User') }}</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($recentRentals as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                            <td class="px-5 py-4 font-medium text-gray-900 dark:text-white">{{ $item->reference }}</td>
                            <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $item->equipment->name ?? '—' }}</td>
                            <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $item->user->name ?? '—' }}</td>
                            <td class="px-5 py-4">
                                <x-ui.badge :variant="\App\Models\Rental::STATUS_BADGE[$item->status] ?? 'default'" :size="'sm'">{{ ucfirst($item->status) }}</x-ui.badge>
                            </td>
                        </tr>
                        @endforeach
                        @if($recentRentals->isEmpty())
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">{{ __('No rentals found') }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-span-12 xl:col-span-6">
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-800 px-5 py-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Active Maintenance') }}</h3>
                <a href="{{ route('admin.technical.maintenances.index') }}" class="text-sm text-brand-500 hover:underline">{{ __('View All') }}</a>
            </div>
            <div class="p-5 space-y-4">
                @php
                    $openMaintenances = \App\Models\Maintenance::with('equipment')->where('status', 'open')->latest()->limit(5)->get();
                @endphp
                @foreach($openMaintenances as $item)
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-white/[0.03] rounded-lg">
                    <div>
                        <div class="font-medium text-gray-900 dark:text-white">{{ $item->equipment->name ?? 'Equipment #' . $item->equipment_id }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $item->description }}</div>
                    </div>
                    <div class="text-right">
                        <x-ui.badge :variant="'warning'" :size="'sm'">{{ ucfirst($item->status) }}</x-ui.badge>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $item->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                @endforeach
                @if($openMaintenances->isEmpty())
                <div class="text-center text-gray-500 dark:text-gray-400 py-8">{{ __('No open maintenance tasks') }}</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-span-12">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Quick Actions') }}</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ route('admin.equipment.create') }}" class="group flex flex-col items-center justify-center p-5 rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.03] hover:border-brand-300 dark:hover:border-brand-700 transition-colors">
                    <div class="w-12 h-12 bg-brand-100 dark:bg-brand-900/30 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <span class="font-medium text-gray-900 dark:text-white">{{ __('Add Equipment') }}</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('New listing') }}</span>
                </a>
                <a href="{{ route('rental.reservations.index') }}" class="group flex flex-col items-center justify-center p-5 rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.03] hover:border-brand-300 dark:hover:border-brand-700 transition-colors">
                    <div class="w-12 h-12 bg-success-100 dark:bg-success-900/30 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <span class="font-medium text-gray-900 dark:text-white">{{ __('Reservations') }}</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $metrics['reservations']['pending'] }} {{ __('pending') }}</span>
                </a>
                <a href="{{ route('admin.rentals.index') }}" class="group flex flex-col items-center justify-center p-5 rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.03] hover:border-brand-300 dark:hover:border-brand-700 transition-colors">
                    <div class="w-12 h-12 bg-warning-100 dark:bg-warning-900/30 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                    </div>
                    <span class="font-medium text-gray-900 dark:text-white">{{ __('Active Rentals') }}</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $metrics['rentals']['active'] }} {{ __('active') }}</span>
                </a>
                <a href="{{ route('admin.technical.maintenances.create') }}" class="group flex flex-col items-center justify-center p-5 rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.03] hover:border-brand-300 dark:hover:border-brand-700 transition-colors">
                    <div class="w-12 h-12 bg-error-100 dark:bg-error-900/30 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-error-600 dark:text-error-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 17l5-5m0 0l-5-5m5 5H4" />
                        </svg>
                    </div>
                    <span class="font-medium text-gray-900 dark:text-white">{{ __('Log Maintenance') }}</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $metrics['maintenance']['open'] }} {{ __('open') }}</span>
                </a>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: [
                    @for ($i = 11; $i >= 0; $i--)
                        '{{ now()->subMonths($i)->format('M Y') }}',
                    @endfor
                ],
                datasets: [{
                    label: '{{ __('Revenue (TND)') }}',
                    data: @json($chartData),
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#3B82F6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Category Chart
    const categoryCtx = document.getElementById('categoryChart');
    if (categoryCtx && @json($equipmentByCategory)) {
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: @json(array_keys($equipmentByCategory)),
                datasets: [{
                    data: @json(array_values($equipmentByCategory)),
                    backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#06B6D4', '#84CC16'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right', labels: { font: { size: 11 } } } }
            }
        });
    }

    // Equipment Status Chart
    const equipStatusCtx = document.getElementById('equipmentStatusChart');
    if (equipStatusCtx && @json($equipmentByStatus)) {
        new Chart(equipStatusCtx, {
            type: 'bar',
            data: {
                labels: @json(array_keys($equipmentByStatus)),
                datasets: [{
                    label: '{{ __('Count') }}',
                    data: @json(array_values($equipmentByStatus)),
                    backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#6B7280'],
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } } }
            }
        });
    }

    // Rental/Reservation Status Chart
    const rentalStatusCtx = document.getElementById('rentalStatusChart');
    if (rentalStatusCtx) {
        new Chart(rentalStatusCtx, {
            type: 'bar',
            data: {
                labels: ['{{ __('Reservations') }}', '{{ __('Rentals') }}'],
                datasets: [
                    {
                        label: '{{ __('Pending') }}',
                        data: [@json($reservationsByStatus['Pending'] ?? 0), @json($rentalsByStatus['Pending'] ?? 0)],
                        backgroundColor: '#F59E0B'
                    },
                    {
                        label: '{{ __('Active/Confirmed') }}',
                        data: [@json($reservationsByStatus['Confirmed'] ?? 0), @json($rentalsByStatus['Active'] ?? 0)],
                        backgroundColor: '#3B82F6'
                    },
                    {
                        label: '{{ __('Completed') }}',
                        data: [@json($reservationsByStatus['Completed'] ?? 0), @json($rentalsByStatus['Completed'] ?? 0)],
                        backgroundColor: '#10B981'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { font: { size: 10 } } } },
                scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } }
            }
        });
    }
});
</script>
@endpush