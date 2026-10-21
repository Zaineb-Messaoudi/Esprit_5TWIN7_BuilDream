<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\Invoice;
use App\Models\Maintenance;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\RentalContract;
use App\Models\RentalExtension;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Contracts\View\View;

class SolarShareDashboardController extends Controller
{
    public function index(): View
    {
        try {
            return $this->renderDashboard();
        } catch (\Exception $e) {
            return $this->renderDashboardWithDefaults();
        }
    }

    protected function renderDashboard(): View
    {
        // Equipment metrics
        $totalEquipment = Equipment::count();
        $publishedEquipment = Equipment::where('status', 'available')
            ->where('approval_status', 'published')
            ->count();
        $draftEquipment = Equipment::where('approval_status', 'draft')->count();
        $pendingEquipment = Equipment::where('approval_status', 'pending')->count();
        $rejectedEquipment = Equipment::where('approval_status', 'rejected')->count();
        $equipmentInMaintenance = Equipment::where('status', 'maintenance')->count();

        // Rental & reservation metrics
        $totalReservations = Reservation::count();
        $pendingReservations = Reservation::where('status', 'pending')->count();
        $confirmedReservations = Reservation::where('status', 'confirmed')->count();
        $cancelledReservations = Reservation::where('status', 'cancelled')->count();

        $activeRentals = Rental::where('status', 'active')->count();
        $completedRentals = Rental::where('status', 'completed')->count();
        $overdueRentals = Rental::where('status', 'overdue')->count();
        $totalRentals = Rental::count();

        // Contract metrics
        $activeContracts = RentalContract::where('status', 'active')->count();
        $pendingContracts = RentalContract::where('status', 'pending')->count();
        $expiredContracts = RentalContract::where('status', 'expired')->count();

        // Extension metrics
        $pendingExtensions = RentalExtension::where('status', 'pending')->count();
        $approvedExtensions = RentalExtension::where('status', 'approved')->count();

        // Revenue metrics
        $totalRevenue = Payment::where('status', 'paid')->sum('amount');
        $pendingRevenue = Payment::where('status', 'pending')->sum('amount');
        $thisMonthRevenue = Payment::where('status', 'paid')
            ->where('payment_date', '>=', now()->startOfMonth())
            ->sum('amount');
        $lastMonthRevenue = Payment::where('status', 'paid')
            ->where('payment_date', '>=', now()->subMonth()->startOfMonth())
            ->where('payment_date', '<', now()->startOfMonth())
            ->sum('amount');

        // Maintenance metrics
        $openMaintenances = Maintenance::where('status', 'open')->count();
        $inProgressMaintenances = Maintenance::where('status', 'in_progress')->count();
        $completedMaintenances = Maintenance::where('status', 'completed')->count();
        $totalMaintenanceCost = Maintenance::sum('cost');

        // User metrics
        $totalUsers = User::whereNotNull('role')->count();
        $totalOwners = User::where('role', 'owner')->count();
        $totalBuyers = User::where('role', 'buyer')->count();
        $totalAdmins = User::where('role', 'admin')->count();
        $newUsersThisMonth = User::whereNotNull('role')
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        // Invoice metrics
        $totalInvoices = Invoice::count();
        $paidInvoices = Invoice::where('status', 'paid')->count();
        $pendingInvoices = Invoice::where('status', 'pending')->count();
        $overdueInvoices = Invoice::where('status', 'overdue')->count();

        // Monthly revenue chart data (last 12 months)
        $monthlyRevenue = Payment::where('status', 'paid')
            ->where('payment_date', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw(
                'YEAR(payment_date) as year, MONTH(payment_date) as month, SUM(amount) as total'
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->mapWithKeys(function ($item) {
                $key = sprintf('%04d-%02d', $item->year, $item->month);

                return [$key => (float) $item->total];
            });

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
            ->limit(6)
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->category->name ?? 'Unknown' => $item->count];
            });

        // Equipment by status
        $equipmentByStatus = Equipment::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                $status = $item->status instanceof \BackedEnum
                    ? $item->status->value
                    : $item->status;

                return [ucfirst((string) $status) => $item->count];
            });

        // Reservations by status
        $reservationsByStatus = Reservation::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                $status = $item->status instanceof \BackedEnum
                    ? $item->status->value
                    : $item->status;

                return [ucfirst((string) $status) => $item->count];
            });

        // Rentals by status
        $rentalsByStatus = Rental::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                $status = $item->status instanceof \BackedEnum
                    ? $item->status->value
                    : $item->status;

                return [ucfirst((string) $status) => $item->count];
            });

        // Recent activity
        $recentReservations = Reservation::with(['equipment', 'user'])
            ->latest('created_at')
            ->limit(5)
            ->get();

        $recentRentals = Rental::with(['equipment', 'user'])
            ->latest('created_at')
            ->limit(5)
            ->get();

        $recentEquipment = Equipment::with('category')
            ->latest('created_at')
            ->limit(5)
            ->get();

        // Top performing equipment
        $topEquipment = Equipment::withCount('rentals')
            ->orderByDesc('rentals_count')
            ->limit(5)
            ->get();

        // Equipment needing attention
        $equipmentNeedingAttention = Equipment::where(function ($query) {
            $query->where('status', 'maintenance')
                ->orWhere('approval_status', 'rejected')
                ->orWhere('approval_status', 'pending');
        })->count();

        // Upcoming contract expirations
        $upcomingExpirations = RentalContract::where('status', 'active')
            ->where('end_date', '<=', now()->addDays(30))
            ->where('end_date', '>=', now())
            ->count();

        // Overdue payments
        $overduePayments = Payment::where('status', 'pending')
            ->where('payment_date', '<', now())
            ->count();

        $data = [
            'metrics' => [
                'equipment' => [
                    'total' => $totalEquipment,
                    'published' => $publishedEquipment,
                    'draft' => $draftEquipment,
                    'pending_review' => $pendingEquipment,
                    'rejected' => $rejectedEquipment,
                    'in_maintenance' => $equipmentInMaintenance,
                    'needing_attention' => $equipmentNeedingAttention,
                ],
                'reservations' => [
                    'total' => $totalReservations,
                    'pending' => $pendingReservations,
                    'confirmed' => $confirmedReservations,
                    'cancelled' => $cancelledReservations,
                ],
                'rentals' => [
                    'total' => $totalRentals,
                    'active' => $activeRentals,
                    'completed' => $completedRentals,
                    'overdue' => $overdueRentals,
                ],
                'contracts' => [
                    'active' => $activeContracts,
                    'pending' => $pendingContracts,
                    'expired' => $expiredContracts,
                    'expiring_soon' => $upcomingExpirations,
                ],
                'extensions' => [
                    'pending' => $pendingExtensions,
                    'approved' => $approvedExtensions,
                ],
                'revenue' => [
                    'total' => $totalRevenue,
                    'pending' => $pendingRevenue,
                    'this_month' => $thisMonthRevenue,
                    'last_month' => $lastMonthRevenue,
                    'growth' => $lastMonthRevenue > 0
                        ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
                        : 0,
                ],
                'maintenance' => [
                    'open' => $openMaintenances,
                    'in_progress' => $inProgressMaintenances,
                    'completed' => $completedMaintenances,
                    'total_cost' => $totalMaintenanceCost,
                ],
                'users' => [
                    'total' => $totalUsers,
                    'owners' => $totalOwners,
                    'buyers' => $totalBuyers,
                    'admins' => $totalAdmins,
                    'new_this_month' => $newUsersThisMonth,
                ],
                'invoices' => [
                    'total' => $totalInvoices,
                    'paid' => $paidInvoices,
                    'pending' => $pendingInvoices,
                    'overdue' => $overdueInvoices,
                ],
            ],
            'chartData' => $chartData,
            'equipmentByCategory' => $equipmentByCategory,
            'equipmentByStatus' => $equipmentByStatus,
            'reservationsByStatus' => $reservationsByStatus,
            'rentalsByStatus' => $rentalsByStatus,
            'recentReservations' => $recentReservations,
            'recentRentals' => $recentRentals,
            'recentEquipment' => $recentEquipment,
            'topEquipment' => $topEquipment,
        ];

        return view('pages.dashboard.solarshare', $data);
    }

    protected function renderDashboardWithDefaults(): View
    {
        $data = [
            'metrics' => [
                'equipment' => ['total' => 0, 'published' => 0, 'draft' => 0, 'pending_review' => 0, 'rejected' => 0, 'in_maintenance' => 0, 'needing_attention' => 0],
                'reservations' => ['total' => 0, 'pending' => 0, 'confirmed' => 0, 'cancelled' => 0],
                'rentals' => ['total' => 0, 'active' => 0, 'completed' => 0, 'overdue' => 0],
                'contracts' => ['active' => 0, 'pending' => 0, 'expired' => 0, 'expiring_soon' => 0],
                'extensions' => ['pending' => 0, 'approved' => 0],
                'revenue' => ['total' => 0, 'pending' => 0, 'this_month' => 0, 'last_month' => 0, 'growth' => 0],
                'maintenance' => ['open' => 0, 'in_progress' => 0, 'completed' => 0, 'total_cost' => 0],
                'users' => ['total' => 0, 'owners' => 0, 'buyers' => 0, 'admins' => 0, 'new_this_month' => 0],
                'invoices' => ['total' => 0, 'paid' => 0, 'pending' => 0, 'overdue' => 0],
            ],
            'chartData' => array_fill(0, 12, 0),
            'equipmentByCategory' => [],
            'equipmentByStatus' => [],
            'reservationsByStatus' => [],
            'rentalsByStatus' => [],
            'recentReservations' => collect(),
            'recentRentals' => collect(),
            'recentEquipment' => collect(),
            'topEquipment' => collect(),
        ];

        return view('pages.dashboard.solarshare', $data);
    }
}
