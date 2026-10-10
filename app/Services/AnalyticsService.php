<?php

namespace App\Services;

use App\Models\AnalyticsMetric;
use App\Models\Equipment;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Record a metric.
     */
    public function recordMetric(
        string $metricName,
        string $category,
        float $value,
        array $dimensions = [],
        string $metricType = 'gauge',
        ?Carbon $recordedAt = null
    ): \App\Models\AnalyticsMetric {
        return AnalyticsMetric::record($metricName, $category, $value, $dimensions, $metricType, $recordedAt);
    }

    /**
     * Increment a counter metric.
     */
    public function incrementMetric(
        string $metricName,
        string $category,
        float $increment = 1,
        array $dimensions = [],
        ?Carbon $recordedAt = null
    ): \App\Models\AnalyticsMetric {
        return AnalyticsMetric::incrementCounter($metricName, $category, $increment, $dimensions, $recordedAt);
    }

    /**
     * Get metrics for a category.
     */
    public function getMetrics(
        string $category,
        ?string $metricName = null,
        ?Carbon $start = null,
        ?Carbon $end = null,
        array $dimensions = []
    ) {
        $query = AnalyticsMetric::category($category);

        if ($metricName) {
            $query->metric($metricName);
        }

        if ($start) {
            $query->where('recorded_at', '>=', $start);
        }
        if ($end) {
            $query->where('recorded_at', '<=', $end);
        }

        if (! empty($dimensions)) {
            foreach ($dimensions as $key => $value) {
                $query->whereJsonContains('dimensions', [$key => $value]);
            }
        }

        return $query->orderBy('recorded_at')->get();
    }

    /**
     * Get aggregated metrics for a time range.
     */
    public function getAggregatedMetrics(
        string $category,
        string $metricName,
        Carbon $start,
        Carbon $end,
        string $interval = 'day', // 'hour', 'day', 'week', 'month'
        string $aggregation = 'avg' // 'sum', 'avg', 'min', 'max', 'count'
    ) {
        $query = AnalyticsMetric::category($category)
            ->metric($metricName)
            ->whereBetween('recorded_at', [$start, $end]);

        $groupBy = match ($interval) {
            'hour' => DB::raw('DATE_FORMAT(recorded_at, "%Y-%m-%d %H:00:00")'),
            'day' => DB::raw('DATE(recorded_at)'),
            'week' => DB::raw('YEARWEEK(recorded_at)'),
            'month' => DB::raw('DATE_FORMAT(recorded_at, "%Y-%m")'),
            default => DB::raw('DATE(recorded_at)'),
        };

        return $query->selectRaw("{$groupBy} as period, {$aggregation}(value) as value")
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    /**
     * Record a rental booking event.
     */
    public function recordBookingEvent(Rental $rental, string $eventType): void
    {
        $dimensions = [
            'rental_id' => $rental->id,
            'equipment_id' => $rental->equipment_id,
            'owner_id' => $rental->equipment->owner_id,
            'renter_id' => $rental->user_id,
            'category_id' => $rental->equipment->category_id,
        ];

        switch ($eventType) {
            case 'created':
                AnalyticsMetric::incrementCounter('bookings_created', 'bookings', 1, $dimensions);
                break;
            case 'confirmed':
                AnalyticsMetric::incrementCounter('bookings_confirmed', 'bookings', 1, $dimensions);
                break;
            case 'cancelled':
                AnalyticsMetric::incrementCounter('bookings_cancelled', 'bookings', 1, $dimensions);
                break;
            case 'started':
                AnalyticsMetric::incrementCounter('rentals_started', 'rentals', 1, $dimensions);
                break;
            case 'completed':
                AnalyticsMetric::incrementCounter('rentals_completed', 'rentals', 1, $dimensions);
                AnalyticsMetric::record('rental_duration_days', 'rentals', $rental->start_date->diffInDays($rental->end_date), [
                    'equipment_id' => $rental->equipment_id,
                    'category_id' => $rental->equipment->category_id,
                ]);
                break;
        }
    }

    /**
     * Record payment event.
     */
    public function recordPaymentEvent(\App\Models\Payment $payment): void
    {
        $dimensions = [
            'payment_id' => $payment->id,
            'reservation_id' => $payment->reservation_id,
            'payment_method' => $payment->payment_method,
            'currency' => $payment->currency ?? 'TND',
        ];

        AnalyticsMetric::record('payment_amount', 'revenue', (float) $payment->amount, $dimensions, 'counter');

        if ($payment->status === 'paid') {
            AnalyticsMetric::incrementCounter('payments_successful', 'revenue', 1, $dimensions);
            AnalyticsMetric::record('revenue_tnd', 'revenue', (float) $payment->amount, [
                'payment_method' => $payment->payment_method,
                'currency' => $payment->currency ?? 'TND',
            ], 'counter');
        } elseif ($payment->status === 'failed') {
            AnalyticsMetric::incrementCounter('payments_failed', 'revenue', 1, $dimensions);
        }
    }

    /**
     * Record equipment utilization.
     */
    public function recordEquipmentUtilization(Equipment $equipment): void
    {
        $activeRentals = Rental::where('equipment_id', $equipment->id)
            ->where('status', 'active')
            ->count();

        $totalDaysInMonth = now()->daysInMonth;
        $rentalDaysThisMonth = Rental::where('equipment_id', $equipment->id)
            ->whereMonth('start_date', now()->month)
            ->whereYear('start_date', now()->year)
            ->sum(DB::raw('DATEDIFF(end_date, start_date) + 1'));

        $utilizationRate = $totalDaysInMonth > 0 ? ($rentalDaysThisMonth / $totalDaysInMonth) * 100 : 0;

        $dimensions = [
            'equipment_id' => $equipment->id,
            'owner_id' => $equipment->owner_id,
            'category_id' => $equipment->category_id,
        ];

        AnalyticsMetric::record('equipment_utilization_rate', 'utilization', $utilizationRate, $dimensions, 'gauge');
        AnalyticsMetric::record('active_rentals', 'utilization', $activeRentals, $dimensions, 'gauge');
        AnalyticsMetric::record('rental_days_this_month', 'utilization', $rentalDaysThisMonth, $dimensions, 'counter');
    }

    /**
     * Record user activity.
     */
    public function recordUserActivity(User $user, string $action): void
    {
        $dimensions = [
            'user_id' => $user->id,
            'role' => $user->role->value ?? 'unknown',
        ];

        AnalyticsMetric::incrementCounter("user_activity_{$action}", 'users', 1, $dimensions);
    }

    /**
     * Get revenue analytics.
     */
    public function getRevenueAnalytics(?Carbon $start = null, ?Carbon $end = null): array
    {
        $start = $start ?? now()->subDays(30);
        $end = $end ?? now();

        $totalRevenue = AnalyticsMetric::category('revenue')
            ->metric('revenue_tnd')
            ->whereBetween('recorded_at', [$start, $end])
            ->sum('value');

        $dailyRevenue = $this->getAggregatedMetrics('revenue', 'revenue_tnd', $start, $end, 'day', 'sum');

        $byMethod = AnalyticsMetric::category('revenue')
            ->metric('revenue_tnd')
            ->whereBetween('recorded_at', [$start, $end])
            ->get()
            ->groupBy('dimensions.payment_method')
            ->map(fn ($items) => $items->sum('value'));

        return [
            'total_revenue' => $totalRevenue,
            'daily_revenue' => $dailyRevenue,
            'by_payment_method' => $byMethod,
            'period' => ['start' => $start, 'end' => $end],
        ];
    }

    /**
     * Get booking analytics.
     */
    public function getBookingAnalytics(?Carbon $start = null, ?Carbon $end = null): array
    {
        $start = $start ?? now()->subDays(30);
        $end = $end ?? now();

        $created = $this->getAggregatedMetrics('bookings', 'bookings_created', $start, $end, 'day', 'sum');
        $confirmed = $this->getAggregatedMetrics('bookings', 'bookings_confirmed', $start, $end, 'day', 'sum');
        $cancelled = $this->getAggregatedMetrics('bookings', 'bookings_cancelled', $start, $end, 'day', 'sum');

        $conversionRate = $this->calculateConversionRate($start, $end);

        return [
            'bookings_created' => $created,
            'bookings_confirmed' => $confirmed,
            'bookings_cancelled' => $cancelled,
            'conversion_rate' => $conversionRate,
            'period' => ['start' => $start, 'end' => $end],
        ];
    }

    /**
     * Get equipment utilization analytics.
     */
    public function getUtilizationAnalytics(?Carbon $start = null, ?Carbon $end = null): array
    {
        $start = $start ?? now()->subDays(30);
        $end = $end ?? now();

        $utilization = $this->getAggregatedMetrics('utilization', 'equipment_utilization_rate', $start, $end, 'day', 'avg');
        $activeRentals = $this->getAggregatedMetrics('utilization', 'active_rentals', $start, $end, 'day', 'avg');

        // Top equipment by utilization
        $topEquipment = AnalyticsMetric::category('utilization')
            ->metric('equipment_utilization_rate')
            ->whereBetween('recorded_at', [$start, $end])
            ->get()
            ->groupBy('dimensions.equipment_id')
            ->map(fn ($items) => [
                'equipment_id' => $items->first()->dimensions['equipment_id'],
                'avg_utilization' => round($items->avg('value'), 2),
            ])
            ->sortByDesc('avg_utilization')
            ->take(10);

        return [
            'daily_utilization' => $utilization,
            'active_rentals' => $activeRentals,
            'top_equipment' => $topEquipment->values()->all(),
            'period' => ['start' => $start, 'end' => $end],
        ];
    }

    /**
     * Calculate booking conversion rate.
     */
    private function calculateConversionRate(Carbon $start, Carbon $end): float
    {
        $created = AnalyticsMetric::category('bookings')
            ->metric('bookings_created')
            ->whereBetween('recorded_at', [$start, $end])
            ->sum('value');

        $confirmed = AnalyticsMetric::category('bookings')
            ->metric('bookings_confirmed')
            ->whereBetween('recorded_at', [$start, $end])
            ->sum('value');

        return $created > 0 ? round(($confirmed / $created) * 100, 1) : 0;
    }

    /**
     * Record a custom event.
     */
    public function recordEvent(
        string $eventName,
        string $category,
        array $dimensions = [],
        float $value = 1,
        string $metricType = 'counter'
    ): void {
        AnalyticsMetric::record($eventName, $category, $value, $dimensions, $metricType);
    }

    /**
     * Get top performing equipment.
     */
    public function getTopEquipment(int $limit = 10, ?Carbon $start = null, ?Carbon $end = null): array
    {
        $start = $start ?? now()->subDays(30);
        $end = $end ?? now();

        $equipmentRevenue = Payment::query()
            ->whereHas('reservation.equipment')
            ->whereBetween('payment_date', [$start, $end])
            ->where('status', 'paid')
            ->with('reservation.equipment')
            ->get()
            ->groupBy('reservation.equipment_id')
            ->map(function ($payments, $equipmentId) {
                $first = $payments->first();

                return [
                    'equipment_id' => $equipmentId,
                    'equipment_name' => $first->reservation->equipment->name ?? 'Unknown',
                    'total_revenue' => round($payments->sum('amount'), 2),
                    'booking_count' => $payments->count(),
                    'avg_booking_value' => round($payments->avg('amount'), 2),
                ];
            })
            ->sortByDesc('total_revenue')
            ->take($limit)
            ->values()
            ->all();

        return $equipmentRevenue;
    }

    /**
     * Get user growth analytics.
     */
    public function getUserGrowthAnalytics(?Carbon $start = null, ?Carbon $end = null): array
    {
        $start = $start ?? now()->subDays(30);
        $end = $end ?? now();

        $registrations = $this->getAggregatedMetrics('users', 'user_activity_registered', $start, $end, 'day', 'sum');

        $byRole = AnalyticsMetric::category('users')
            ->whereBetween('recorded_at', [$start, $end])
            ->get()
            ->groupBy('dimensions.role')
            ->map(fn ($items) => $items->sum('value'));

        $activeUsers = User::where('created_at', '<=', $end)
            ->where(function ($q) use ($start) {
                $q->whereNull('last_login_at')
                    ->orWhere('last_login_at', '>=', $start);
            })
            ->count();

        return [
            'daily_registrations' => $registrations,
            'by_role' => $byRole,
            'active_users' => $activeUsers,
            'period' => ['start' => $start, 'end' => $end],
        ];
    }
}
