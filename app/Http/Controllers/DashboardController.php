<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Reservation;
use App\Models\Rental;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Maintenance;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private const SECTOR_DASHBOARDS = [
        'stocks' => [
            'title' => 'Stocks & Investments',
            'description' => 'A live-style overview of portfolio performance and market activity.',
            'metrics' => [
                ['label' => 'Portfolio value', 'value' => '$284,590', 'change' => '+8.4%', 'tone' => 'success'],
                ['label' => 'Today\'s return', 'value' => '$2,840', 'change' => '+1.2%', 'tone' => 'success'],
                ['label' => 'Open positions', 'value' => '18', 'change' => '3 watchlisted', 'tone' => 'primary'],
                ['label' => 'Available cash', 'value' => '$32,450', 'change' => '11.4% of portfolio', 'tone' => 'primary'],
            ],
            'series' => [32, 38, 35, 49, 43, 54, 48, 63, 58, 70, 66, 82],
            'series_label' => 'Portfolio value',
            'rows' => [
                ['name' => 'SolarEdge Technologies', 'symbol' => 'SEDG', 'value' => '$42,860', 'change' => '+4.82%', 'tone' => 'success'],
                ['name' => 'Enphase Energy', 'symbol' => 'ENPH', 'value' => '$36,420', 'change' => '+2.16%', 'tone' => 'success'],
                ['name' => 'First Solar', 'symbol' => 'FSLR', 'value' => '$29,880', 'change' => '-0.74%', 'tone' => 'error'],
                ['name' => 'NextEra Energy', 'symbol' => 'NEE', 'value' => '$24,150', 'change' => '+1.39%', 'tone' => 'success'],
            ],
        ],
        'finance' => [
            'title' => 'Financial Overview',
            'description' => 'Track income, operating costs, and cash position at a glance.',
            'metrics' => [
                ['label' => 'Total revenue', 'value' => '$128,460', 'change' => '+12.8%', 'tone' => 'success'],
                ['label' => 'Operating expenses', 'value' => '$46,280', 'change' => '-3.2%', 'tone' => 'success'],
                ['label' => 'Net income', 'value' => '$82,180', 'change' => '+18.6%', 'tone' => 'success'],
                ['label' => 'Cash on hand', 'value' => '$214,500', 'change' => '6.2 months runway', 'tone' => 'primary'],
            ],
            'series' => [28, 34, 31, 43, 39, 53, 47, 58, 54, 69, 65, 78],
            'series_label' => 'Net cash flow',
            'rows' => [
                ['name' => 'Solar installation revenue', 'symbol' => 'Income', 'value' => '$54,200', 'change' => '+14.3%', 'tone' => 'success'],
                ['name' => 'Equipment and inventory', 'symbol' => 'Expense', 'value' => '$21,460', 'change' => '-2.1%', 'tone' => 'success'],
                ['name' => 'Maintenance contracts', 'symbol' => 'Income', 'value' => '$18,840', 'change' => '+8.7%', 'tone' => 'success'],
                ['name' => 'Operations and logistics', 'symbol' => 'Expense', 'value' => '$12,350', 'change' => '+1.4%', 'tone' => 'error'],
            ],
        ],
        'sales' => [
            'title' => 'Sales Performance',
            'description' => 'Monitor sales momentum, orders, and team performance.',
            'metrics' => [
                ['label' => 'Gross sales', 'value' => '$96,840', 'change' => '+14.8%', 'tone' => 'success'],
                ['label' => 'Orders won', 'value' => '1,284', 'change' => '+8.2%', 'tone' => 'success'],
                ['label' => 'Conversion rate', 'value' => '3.64%', 'change' => '+0.6%', 'tone' => 'success'],
                ['label' => 'Average order value', 'value' => '$75.42', 'change' => '+4.1%', 'tone' => 'success'],
            ],
            'series' => [24, 35, 30, 44, 40, 52, 48, 58, 53, 71, 64, 84],
            'series_label' => 'Sales revenue',
            'rows' => [
                ['name' => 'Amira Ben Salem', 'symbol' => 'Residential', 'value' => '$24,860', 'change' => '32 orders', 'tone' => 'success'],
                ['name' => 'Youssef Trabelsi', 'symbol' => 'Commercial', 'value' => '$21,420', 'change' => '18 orders', 'tone' => 'success'],
                ['name' => 'Maya Haddad', 'symbol' => 'Residential', 'value' => '$18,950', 'change' => '27 orders', 'tone' => 'primary'],
                ['name' => 'Karim Mansour', 'symbol' => 'Maintenance', 'value' => '$14,380', 'change' => '41 orders', 'tone' => 'primary'],
            ],
        ],
        'logistics' => [
            'title' => 'Logistics & Deliveries',
            'description' => 'Keep track of shipments, delivery progress, and warehouse operations.',
            'metrics' => [
                ['label' => 'Active shipments', 'value' => '248', 'change' => '32 in transit', 'tone' => 'primary'],
                ['label' => 'Delivered today', 'value' => '86', 'change' => '+12.5%', 'tone' => 'success'],
                ['label' => 'On-time delivery', 'value' => '96.8%', 'change' => '+1.4%', 'tone' => 'success'],
                ['label' => 'Needs attention', 'value' => '7', 'change' => '2 delayed', 'tone' => 'error'],
            ],
            'series' => [30, 36, 33, 45, 41, 55, 50, 62, 57, 72, 69, 82],
            'series_label' => 'Shipments delivered',
            'rows' => [
                ['name' => 'Solar panel kit · ORD-2084', 'symbol' => 'Tunis warehouse', 'value' => 'In transit', 'change' => 'Arrives 2:30 PM', 'tone' => 'primary'],
                ['name' => 'Inverter bundle · ORD-2081', 'symbol' => 'Sfax warehouse', 'value' => 'Delivered', 'change' => 'Today, 10:42 AM', 'tone' => 'success'],
                ['name' => 'Battery storage · ORD-2079', 'symbol' => 'Sousse warehouse', 'value' => 'Delayed', 'change' => 'Review required', 'tone' => 'error'],
                ['name' => 'Mounting hardware · ORD-2076', 'symbol' => 'Tunis warehouse', 'value' => 'Preparing', 'change' => 'Dispatch today', 'tone' => 'primary'],
            ],
        ],
    ];

    public function index(): View
    {
        return view('dashboard');
    }

    public function sector(string $dashboard): View
    {
        abort_unless(isset(self::SECTOR_DASHBOARDS[$dashboard]), 404);

        return view('pages.dashboard.sector', self::SECTOR_DASHBOARDS[$dashboard]);
    }

    public function ecommerce(): View
    {
        // Fetch real SolarShare statistics
        $totalEquipment = Equipment::count();
        $availableEquipment = Equipment::where('status', 'available')->where('approval_status', 'published')->count();
        $totalReservations = Reservation::count();
        $pendingReservations = Reservation::where('status', 'pending')->count();
        $activeRentals = Rental::where('status', 'active')->count();
        $completedRentals = Rental::where('status', 'completed')->count();
        $totalRevenue = Payment::where('status', 'paid')->sum('amount');
        $pendingRevenue = Payment::where('status', 'pending')->sum('amount');
        $totalInvoices = Invoice::count();
        $paidInvoices = Invoice::where('status', 'paid')->count();
        $equipmentInMaintenance = Equipment::where('status', 'maintenance')->count();
        $totalMaintenanceCost = Maintenance::sum('cost');

        // Monthly revenue for chart (last 12 months)
        $monthlyRevenue = Payment::where('status', 'paid')
            ->where('payment_date', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw('YEAR(payment_date) as year, MONTH(payment_date) as month, SUM(amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->mapWithKeys(function ($item) {
                $key = sprintf('%04d-%02d', $item->year, $item->month);
                return [$key => (float) $item->total];
            });

        // Fill missing months with 0
        $chartData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i)->startOfMonth();
            $key = $date->format('Y-m');
            $chartData[] = $monthlyRevenue->get($key, 0);
        }

        // Equipment by category
        $equipmentByCategory = Equipment::with('category')
            ->selectRaw('category_id, count(*) as count')
            ->groupBy('category_id')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->category->name ?? 'Unknown' => $item->count];
            });

        // Equipment by status
        $equipmentByStatus = Equipment::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [ucfirst($item->status) => $item->count];
            });

        // Reservations by status
        $reservationsByStatus = Reservation::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [ucfirst($item->status) => $item->count];
            });

        // Rentals by status
        $rentalsByStatus = Rental::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [ucfirst($item->status) => $item->count];
            });

        // Recent orders (rentals with payments)
        $recentOrders = Rental::with(['equipment', 'user', 'contract'])
            ->latest('created_at')
            ->limit(5)
            ->get();

        // Top equipment by rental count
        $topEquipment = Equipment::withCount('rentals')
            ->orderByDesc('rentals_count')
            ->limit(5)
            ->get();

        // Customer demographics (simplified)
        $customersByRole = \App\Models\User::selectRaw('role, count(*) as count')
            ->whereNotNull('role')
            ->groupBy('role')
            ->get()
            ->mapWithKeys(function ($item) {
                $role = $item->role ?? $item['role'] ?? '';
                if ($role instanceof \App\Enums\UserRole) {
                    $role = $role->value;
                }
                $count = $item->count ?? $item['count'] ?? 0;
                return [$role => $count];
            });

        $data = [
            'title' => 'E-commerce Dashboard',
            'metrics' => [
                ['label' => 'Total Equipment', 'value' => $totalEquipment, 'change' => $availableEquipment . ' available', 'tone' => 'primary'],
                ['label' => 'Active Rentals', 'value' => $activeRentals, 'change' => $completedRentals . ' completed', 'tone' => 'success'],
                ['label' => 'Total Revenue', 'value' => number_format($totalRevenue, 2) . ' TND', 'change' => number_format($pendingRevenue, 2) . ' TND pending', 'tone' => 'success'],
                ['label' => 'Maintenance', 'value' => $equipmentInMaintenance, 'change' => number_format($totalMaintenanceCost, 2) . ' TND total cost', 'tone' => $equipmentInMaintenance > 0 ? 'warning' : 'success'],
            ],
            'chartData' => $chartData,
            'chartLabel' => 'Monthly Revenue (TND)',
            'equipmentByCategory' => $equipmentByCategory,
            'equipmentByStatus' => $equipmentByStatus,
            'reservationsByStatus' => $reservationsByStatus,
            'rentalsByStatus' => $rentalsByStatus,
            'recentOrders' => $recentOrders,
            'topEquipment' => $topEquipment,
            'customersByRole' => $customersByRole,
        ];

        return view('pages.dashboard.ecommerce', $data);
    }
}
