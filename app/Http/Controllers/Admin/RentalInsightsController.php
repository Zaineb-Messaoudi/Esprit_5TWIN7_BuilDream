<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rental;
use App\Services\RentalInsightsService;
use Illuminate\View\View;

/**
 * Back office pages for the advanced features of the rental module.
 * Protected by the admin-only middleware of the "admin." route group.
 *
 * admin.rental-insights.dashboard | GET /admin/rental-insights          | dashboard() analytics
 * admin.rental-insights.overdue   | GET /admin/rental-insights/overdue  | overdue()   late rentals and fees
 */
class RentalInsightsController extends Controller
{
    // Laravel injects the service automatically (same pattern as UserController with UserService)
    public function __construct(protected RentalInsightsService $insights) {}

    /** Analytics dashboard: figures, rentals by status, last months, top equipment. */
    public function dashboard(): View
    {
        $statuses = $this->insights->statusBreakdown();
        $months   = $this->insights->monthlyActivity(6);

        return view('pages.admin.rental-insights.dashboard', [
            'kpis'         => $this->insights->kpis(),
            'statuses'     => $statuses,
            // Largest value of each list: the longest bar of a chart = 100%
            'statusMax'    => max(1, collect($statuses)->max('count')),
            'months'       => $months,
            'monthMax'     => max(1, collect($months)->max('count')),
            'topEquipment' => $this->insights->topEquipment(5),
            'title'        => __('Rental Analytics'),
        ]);
    }

    /** Overdue rentals with the number of late days and the late fee of each one. */
    public function overdue(): View
    {
        // One row per overdue rental, with the calculations done by the service
        $rows = $this->insights->overdueRentals()->map(fn (Rental $rental) => [
            'rental'     => $rental,
            'late_days'  => $this->insights->lateDays($rental),
            'daily_rate' => $this->insights->dailyRate($rental),
            'late_fee'   => $this->insights->lateFee($rental),
        ]);

        return view('pages.admin.rental-insights.overdue', [
            'rows'       => $rows,
            'totalFee'   => round($rows->sum('late_fee'), 2),
            'multiplier' => RentalInsightsService::LATE_FEE_MULTIPLIER,
            'title'      => __('Overdue Rentals'),
        ]);
    }
}