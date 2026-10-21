<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RentalStatus;
use App\Http\Controllers\Controller;
use App\Models\Rental;
use App\Services\LateRiskService;
use App\Services\RentalInsightsService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Back office pages for the advanced features of the rental module.
 * Protected by the admin-only middleware of the "admin." route group.
 *
 * admin.rental-insights.dashboard | GET /admin/rental-insights          | dashboard() analytics
 * admin.rental-insights.overdue    | GET /admin/rental-insights/overdue  | overdue()   late rentals, fees and delay risk
 * admin.rental-insights.export    | GET /admin/rental-insights/export   | export()    CSV file of the rentals (Excel, French format)
 */
class RentalInsightsController extends Controller
{
    // Laravel injects the two services automatically (same pattern as UserController with UserService)
    public function __construct(
        protected RentalInsightsService $insights,
        protected LateRiskService $risk,
    ) {}

    /** Analytics dashboard: figures, rentals by status, last months, top equipment. */
    public function dashboard(): View
    {
        $statuses = $this->insights->statusBreakdown();
        $months = $this->insights->monthlyActivity(6);

        return view('pages.admin.rental-insights.dashboard', [
            'kpis' => $this->insights->kpis(),
            'statuses' => $statuses,
            // Largest value of each list: the longest bar of a chart = 100%
            'statusMax' => max(1, collect($statuses)->max('count')),
            'months' => $months,
            'monthMax' => max(1, collect($months)->max('count')),
            'topEquipment' => $this->insights->topEquipment(5),
            'title' => __('Rental Analytics'),
        ]);
    }

    /** Overdue rentals with the number of late days, the late fee and the delay risk of each one. */
    public function overdue(): View
    {
        // One row per overdue rental, with the calculations done by the services
        $rows = $this->insights->overdueRentals()->map(fn (Rental $rental) => [
            'rental' => $rental,
            'late_days' => $this->insights->lateDays($rental),
            'daily_rate' => $this->insights->dailyRate($rental),
            'late_fee' => $this->insights->lateFee($rental),
            'risk' => $this->risk->assess($rental),
        ]);

        return view('pages.admin.rental-insights.overdue', [
            'rows' => $rows,
            'totalFee' => round($rows->sum('late_fee'), 2),
            'multiplier' => RentalInsightsService::LATE_FEE_MULTIPLIER,
            'title' => __('Overdue Rentals'),
        ]);
    }

    /**
     * CSV export of the rentals (opens in Excel).
     * It follows the same filters as the rentals list: ?status=active&search=RNT-2026
     * The file is written line by line (200 rentals at a time), so a big list never fills the memory.
     *
     * EXCEL AND THE LANGUAGE OF WINDOWS: Excel splits the columns with the "list separator" of the
     * computer. With a French Windows it is ";" and decimals are written with a comma (12,50).
     * A file separated by commas then opens as ONE single column. So the default format is the
     * French one (";" and decimal comma). Add ?delimiter=comma for the international format
     * (commas and decimal points), used by English-language Excel and by most programs.
     */
    public function export(Request $request): StreamedResponse
    {
        $international = $request->query('delimiter') === 'comma';
        $delimiter = $international ? ',' : ';';
        $decimal = $international ? '.' : ',';

        $query = Rental::query()->with(['user', 'equipment', 'contract'])->withCount('extensions');

        // Same filters as the list
        if ($request->filled('status') && RentalStatus::tryFrom($request->status)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', '%'.$search.'%')
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('equipment', fn ($e) => $e->where('name', 'like', '%'.$search.'%'));
            });
        }

        $filename = 'rentals-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($query, $delimiter, $decimal) {
            $out = fopen('php://output', 'w');

            // The BOM tells Excel that the file is UTF-8 (accents and Arabic display correctly)
            fwrite($out, "\xEF\xBB\xBF");

            $this->writeCsvLine($out, [
                'Reference', 'Renter', 'Renter email', 'Equipment', 'Start date', 'End date', 'Days',
                'Status', 'Total amount (TND)', 'Contract', 'Contract status', 'Deposit status',
                'Extensions', 'Days late', 'Late fee (TND)',
            ], $delimiter);

            $query->orderBy('id')->chunk(200, function ($rentals) use ($out, $delimiter, $decimal) {
                foreach ($rentals as $rental) {
                    $this->writeCsvLine($out, [
                        $rental->reference,
                        $rental->user?->name,
                        $rental->user?->email,
                        $rental->equipment?->name,
                        $rental->start_date->format('Y-m-d'),
                        $rental->end_date->format('Y-m-d'),
                        (int) $rental->start_date->diffInDays($rental->end_date),
                        $rental->status->label(),
                        number_format((float) $rental->total_amount, 2, $decimal, ''),
                        $rental->contract?->contract_number,
                        $rental->contract?->contract_status?->label(),
                        $rental->contract?->deposit_status?->label(),
                        $rental->extensions_count,
                        $this->insights->lateDays($rental),
                        number_format($this->insights->lateFee($rental), 2, $decimal, ''),
                    ], $delimiter);
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Writes one line of the CSV file, with the chosen column separator. */
    private function writeCsvLine($handle, array $cells, string $delimiter): void
    {
        // The last argument ('') is the "escape" character: PHP 8.4 wants it written explicitly
        fputcsv($handle, array_map(fn ($cell) => $this->csvSafe($cell), $cells), $delimiter, '"', '');
    }

    /**
     * Protection against "CSV injection": a text that starts with = + - or @ would be run as a
     * formula by Excel (a renter could choose such a name). An apostrophe in front makes it plain text.
     */
    private function csvSafe(mixed $value): mixed
    {
        if (is_string($value) && preg_match('/^[=+\-@\t\r]/', $value)) {
            return "'".$value;
        }

        return $value;
    }
}
