<?php

namespace App\Services;

use App\Enums\ContractStatus;
use App\Enums\DepositStatus;
use App\Enums\ExtensionStatus;
use App\Enums\RentalStatus;
use App\Models\Equipment;
use App\Models\Rental;
use App\Models\RentalContract;
use App\Models\RentalExtension;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Advanced features of the rental module (Student 3):
 *   1) OVERDUE tracking + automatic LATE FEE calculation
 *   2) ANALYTICS: figures and statistics about rentals, contracts, extensions and deposits
 *
 * All the calculations live here (not in the controller or the views),
 * so they can be reused and tested on their own.
 */
class RentalInsightsService
{
    /**
     * A late day costs the normal daily rate multiplied by this number.
     * 1.0 = same price as a normal day. Use 1.5 for a 50% penalty, for example.
     * (The contract terms say: "Late returns are charged by the day".)
     */
    public const LATE_FEE_MULTIPLIER = 1.0;

    // ------------------------------------------------------------------
    // 1) Overdue rentals and late fees
    // ------------------------------------------------------------------

    /**
     * Rentals that are still ACTIVE although their end date is already past:
     * the renter has not returned the equipment on time.
     */
    public function overdueRentals(): Collection
    {
        return Rental::query()
            ->where('status', RentalStatus::ACTIVE->value)
            ->whereDate('end_date', '<', today())
            ->with(['user', 'equipment'])   // load renters and equipment in 2 queries (no N+1)
            ->orderBy('end_date')           // the most late rentals first
            ->get();
    }

    /** Number of days a rental is late (0 if it is not overdue). */
    public function lateDays(Rental $rental): int
    {
        if ($rental->status !== RentalStatus::ACTIVE || $rental->end_date->gte(today())) {
            return 0;
        }

        return (int) $rental->end_date->diffInDays(today());
    }

    /** Price of one rental day: the rental total divided by its number of days. */
    public function dailyRate(Rental $rental): float
    {
        $days = max(1, (int) $rental->start_date->diffInDays($rental->end_date));

        return round((float) $rental->total_amount / $days, 2);
    }

    /** Late fee = daily rate x late days x multiplier. */
    public function lateFee(Rental $rental): float
    {
        return round($this->dailyRate($rental) * $this->lateDays($rental) * self::LATE_FEE_MULTIPLIER, 2);
    }

    // ------------------------------------------------------------------
    // 2) Analytics
    // ------------------------------------------------------------------

    /** Main figures shown on the analytics dashboard. */
    public function kpis(): array
    {
        $approved = RentalExtension::where('status', ExtensionStatus::APPROVED->value)->count();
        $rejected = RentalExtension::where('status', ExtensionStatus::REJECTED->value)->count();
        $decided = $approved + $rejected;

        return [
            'total_rentals' => Rental::count(),
            'active_rentals' => Rental::where('status', RentalStatus::ACTIVE->value)->count(),
            'overdue_rentals' => Rental::where('status', RentalStatus::ACTIVE->value)
                ->whereDate('end_date', '<', today())->count(),
            // Revenue = rentals that are running or finished (extensions are already in the total)
            'revenue' => (float) Rental::whereIn('status', [
                RentalStatus::ACTIVE->value,
                RentalStatus::COMPLETED->value,
            ])->sum('total_amount'),
            'deposits_held' => (float) RentalContract::where('deposit_status', DepositStatus::HELD->value)
                ->sum('deposit_amount'),
            'pending_signatures' => RentalContract::where('contract_status', ContractStatus::DRAFT->value)->count(),

            // Contracts by status
            'contracts' => [
                'draft' => RentalContract::where('contract_status', ContractStatus::DRAFT->value)->count(),
                'signed' => RentalContract::where('contract_status', ContractStatus::SIGNED->value)->count(),
                'terminated' => RentalContract::where('contract_status', ContractStatus::TERMINATED->value)->count(),
            ],

            // Extension requests by status + approval rate (only decided requests count)
            'extensions' => [
                'pending' => RentalExtension::where('status', ExtensionStatus::PENDING->value)->count(),
                'approved' => $approved,
                'rejected' => $rejected,
                'approval_rate' => $decided > 0 ? round($approved / $decided * 100) : null,
            ],
        ];
    }

    /**
     * Number of rentals per status, ready to display:
     * [ ['label' => 'Active', 'count' => 4], ... ] (statuses without rentals show 0).
     */
    public function statusBreakdown(): array
    {
        // [ 'active' => 4, 'completed' => 4, ... ]
        $counts = Rental::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $rows = [];
        foreach (RentalStatus::cases() as $status) {
            $rows[] = [
                'label' => $status->label(),
                'count' => (int) ($counts[$status->value] ?? 0),
            ];
        }

        return $rows;
    }

    /**
     * Rentals starting in each of the last $months months (cancelled ones are ignored):
     * [ ['label' => 'Oct 2026', 'count' => 5, 'revenue' => 340.0], ... ] oldest month first.
     */
    public function monthlyActivity(int $months = 6): array
    {
        $from = Carbon::now()->subMonths($months - 1)->startOfMonth();

        // One query, then the grouping is done in PHP (works with any database)
        $rentals = Rental::query()
            ->where('start_date', '>=', $from)
            ->where('status', '!=', RentalStatus::CANCELLED->value)
            ->get(['start_date', 'total_amount'])
            ->groupBy(fn (Rental $rental) => $rental->start_date->format('Y-m'));

        $rows = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $group = $rentals->get($month->format('Y-m'), collect());

            $rows[] = [
                'label' => $month->format('M Y'),
                'count' => $group->count(),
                'revenue' => round((float) $group->sum('total_amount'), 2),
            ];
        }

        return $rows;
    }

    /** The equipment rented most often: [ ['name' => ..., 'rentals' => 3, 'revenue' => 120.0], ... ]. */
    public function topEquipment(int $limit = 5): Collection
    {
        $stats = Rental::query()
            ->select('equipment_id', DB::raw('count(*) as rentals_count'), DB::raw('sum(total_amount) as revenue'))
            ->where('status', '!=', RentalStatus::CANCELLED->value)
            ->groupBy('equipment_id')
            ->orderByDesc('rentals_count')
            ->limit($limit)
            ->get();

        // One extra query for all the names
        $names = Equipment::whereIn('id', $stats->pluck('equipment_id'))->pluck('name', 'id');

        return $stats->map(fn ($row) => [
            'name' => $names[$row->equipment_id] ?? ('Equipment #'.$row->equipment_id),
            'rentals' => (int) $row->rentals_count,
            'revenue' => round((float) $row->revenue, 2),
        ]);
    }
}
