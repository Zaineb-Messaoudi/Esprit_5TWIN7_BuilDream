<?php

namespace App\Http\Controllers;

use App\Events\EquipmentReturned;
use App\Models\Equipment;
use App\Models\Inspection;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\RentalContract;
use App\Models\RentalExtension;
use App\Models\Reservation;
use App\Support\FrontDemo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Renders any page declared in config/front.php (section-based pages). */
class PageController extends Controller
{
    public function buyerReservation(Request $request, Reservation $reservation): View
    {
        abort_unless($request->user()->isBuyer() && (int) $reservation->user_id === (int) $request->user()->id, 403);
        $reservation->load(['equipment.owner', 'payments', 'invoice', 'rental.contract']);

        return view('pages.front.live-record-detail', [
            'title' => __('Reservation details'), 'record' => $reservation, 'recordType' => 'reservation', 'owner' => false,
        ]);
    }

    public function ownerReservation(Request $request, Reservation $reservation): View
    {
        abort_unless($request->user()->isOwner() && (int) $reservation->equipment?->owner_id === (int) $request->user()->id, 403);
        $reservation->load(['equipment', 'user', 'payments', 'invoice', 'rental.contract']);

        return view('pages.front.live-record-detail', [
            'title' => __('Reservation details'), 'record' => $reservation, 'recordType' => 'reservation', 'owner' => true,
        ]);
    }

    public function buyerRental(Request $request, Rental $rental): View
    {
        abort_unless($request->user()->isBuyer() && (int) $rental->user_id === (int) $request->user()->id, 403);
        $rental->load(['equipment.owner', 'reservation.payments', 'reservation.invoice', 'contract', 'extensions']);

        return view('pages.front.live-record-detail', [
            'title' => __('Rental details'), 'record' => $rental, 'recordType' => 'rental', 'owner' => false,
        ]);
    }

    public function ownerRental(Request $request, Rental $rental): View
    {
        abort_unless($request->user()->isOwner() && (int) $rental->equipment?->owner_id === (int) $request->user()->id, 403);
        $rental->load(['equipment', 'user', 'reservation.payments', 'reservation.invoice', 'contract', 'extensions']);

        return view('pages.front.live-record-detail', [
            'title' => __('Rental details'), 'record' => $rental, 'recordType' => 'rental', 'owner' => true,
        ]);
    }

    /** Export rental contract as PDF for the front office. */
    public function exportContractPdf(Request $request, Rental $rental): \Illuminate\Http\Response
    {
        abort_unless($rental->contract, 404);

        // Buyer or owner can download
        abort_unless(
            $request->user()->isBuyer() && (int) $rental->user_id === (int) $request->user()->id ||
            $request->user()->isOwner() && (int) $rental->equipment?->owner_id === (int) $request->user()->id,
            403
        );

        $rental->load(['equipment.category', 'user', 'contract']);
        $pdf = Pdf::loadView('pdf.rental-contract', ['contract' => $rental->contract]);

        return $pdf->download($rental->contract->contract_number.'.pdf');
    }

    /** Show the return equipment form (inspection) for an active rental. */
    public function showReturnForm(Request $request, Rental $rental): View|RedirectResponse
    {
        // Buyer (renter) or admin can initiate return; owner can also view
        abort_unless(
            $request->user()->isAdmin() ||
            ($request->user()->isBuyer() && (int) $rental->user_id === (int) $request->user()->id) ||
            ($request->user()->isOwner() && (int) $rental->equipment?->owner_id === (int) $request->user()->id),
            403
        );

        // Only active or completed rentals can be returned
        abort_unless(in_array($rental->status->value, ['active', 'completed']), 409, 'Rental cannot be returned in its current state.');

        // Check if inspection already exists
        $existingInspection = Inspection::where('rental_id', $rental->id)->first();
        if ($existingInspection) {
            return redirect()->route('technical.inspections.show', $existingInspection)
                ->with('status', 'inspection-already-exists');
        }

        $rental->load(['equipment.category', 'user', 'contract']);

        return view('pages.front.rental-return', [
            'title' => __('Return equipment'),
            'rental' => $rental,
            'owner' => $request->user()->isOwner(),
            'isBuyer' => $request->user()->isBuyer(),
        ]);
    }

    /** Process the return equipment form submission (buyer initiates return). */
    public function processReturn(Request $request, Rental $rental): RedirectResponse
    {
        // Buyer initiates return, owner can also process (for manual inspection)
        abort_unless(
            $request->user()->isAdmin() ||
            ($request->user()->isBuyer() && (int) $rental->user_id === (int) $request->user()->id) ||
            ($request->user()->isOwner() && (int) $rental->equipment?->owner_id === (int) $request->user()->id),
            403
        );

        abort_unless(in_array($rental->status->value, ['active', 'completed']), 409);

        $data = $request->validate([
            'condition_before' => ['required', 'string', 'max:255'],
            'condition_after' => ['required', 'string', 'max:255'],
            'damage_detected' => ['required', 'boolean'],
            'comments' => ['nullable', 'string', 'max:10000'],
        ]);

        DB::transaction(function () use ($rental, $data) {
            // Create the inspection
            $inspection = Inspection::create([
                'equipment_id' => $rental->equipment_id,
                'rental_id' => $rental->id,
                'inspection_date' => now(),
                'condition_before' => $data['condition_before'],
                'condition_after' => $data['condition_after'],
                'damage_detected' => $data['damage_detected'],
                'comments' => $data['comments'] ?? null,
            ]);

            // Update rental status to completed if it was active
            if ($rental->status->value === 'active') {
                $rental->update(['status' => \App\Enums\RentalStatus::COMPLETED]);
            }
        });

        // Fire real-time notification for equipment returned
        $rental->load('equipment', 'user');
        $inspection = $rental->inspections->first();
        EquipmentReturned::dispatch($rental, $inspection, $rental->equipment->owner, $rental->user);

        return redirect()->route(
            $request->user()->isBuyer() ? 'front.buyer-rental-detail' : 'front.my-rental-detail',
            $rental->reference
        )->with('status', 'equipment-returned');
    }

    public function __invoke(Request $request): View|RedirectResponse
    {
        $slug = $request->route('slug');
        $page = config("front.pages.$slug");
        abort_unless($page, 404);

        if (in_array($slug, ['reserve', 'booking-summary', 'payment', 'confirmed', 'invoice'], true)) {
            abort_unless($request->user()?->isBuyer(), 403);
            $equipmentId = $request->integer('equipment', 1);
            $equipment = FrontDemo::findEquipment($equipmentId);
            abort_unless($equipment, 404);

            $reservation = null;
            if ($slug !== 'reserve') {
                $reservation = Reservation::with(['equipment', 'payments', 'invoice', 'rental.contract'])
                    ->where('user_id', $request->user()->id)
                    ->where('equipment_id', $equipmentId)
                    ->findOrFail($request->integer('reservation'));
                if ($slug === 'invoice') {
                    abort_unless($reservation->invoice, 404);
                }
            }

            $blockedIntervals = Reservation::query()
                ->where('equipment_id', $equipmentId)
                ->where('status', '!=', 'cancelled')
                ->get(['start_date', 'end_date'])
                ->map(fn (Reservation $booking) => [
                    'start' => $booking->start_date->toDateString(),
                    'end' => $booking->end_date->toDateString(),
                ])->concat(Rental::query()
                ->where('equipment_id', $equipmentId)
                ->whereNotIn('status', ['cancelled', 'completed'])
                ->get(['start_date', 'end_date'])
                ->map(fn (Rental $rental) => [
                    'start' => $rental->start_date->toDateString(),
                    'end' => $rental->end_date->toDateString(),
                ]))
                ->values()
                ->all();

            return view('pages.front.booking-flow-live', [
                'title' => __($page['title']),
                'slug' => $slug,
                'item' => $equipment,
                'reservation' => $reservation,
                'startDate' => $request->query('start', ''),
                'endDate' => $request->query('end', ''),
                'blockedIntervals' => $blockedIntervals,
            ]);
        }

        if (($page['role'] ?? null) === 'owner') {
            abort_unless($request->user()?->isOwner(), 403);
        } elseif (($page['role'] ?? null) === 'buyer') {
            abort_unless($request->user()?->isBuyer(), 403);
        }

        if (in_array($slug, ['my-maintenance', 'my-inspections'], true)) {
            $ready = class_exists(\App\Models\Equipment::class)
                && Schema::hasTable('equipment')
                && Schema::hasColumn('equipment', 'owner_id')
                && Schema::hasTable('maintenances')
                && Schema::hasTable('maintenance_reports')
                && Schema::hasTable('inspections');

            if (! $ready) {
                return view('pages.front.technical-pending', [
                    'title' => __($page['title']),
                ]);
            }

            return redirect()->route($slug === 'my-maintenance'
                ? 'technical.maintenances.index'
                : 'technical.inspections.index');
        }

        if (in_array($slug, ['my-reservations', 'my-rentals', 'my-contract', 'my-extensions', 'my-payments', 'my-earnings'], true)) {
            $owner = $request->user()->isOwner();
            $reservations = Reservation::with(['equipment', 'user', 'payments', 'invoice'])
                ->when($owner,
                    fn ($query) => $query->whereHas('equipment', fn ($equipment) => $equipment->where('owner_id', $request->user()->id)),
                    fn ($query) => $query->where('user_id', $request->user()->id))
                ->latest()->get();
            $rentals = Rental::with(['equipment', 'user', 'contract', 'extensions'])
                ->when($owner,
                    fn ($query) => $query->whereHas('equipment', fn ($equipment) => $equipment->where('owner_id', $request->user()->id)),
                    fn ($query) => $query->where('user_id', $request->user()->id))
                ->latest()->get();
            $payments = Payment::with('reservation.equipment')
                ->whereHas('reservation', function ($query) use ($owner, $request): void {
                    $owner
                        ? $query->whereHas('equipment', fn ($equipment) => $equipment->where('owner_id', $request->user()->id))
                        : $query->where('user_id', $request->user()->id);
                })->latest()->get();
            $contracts = RentalContract::with('rental.equipment', 'rental.user')
                ->whereHas('rental', function ($query) use ($owner, $request): void {
                    $owner
                        ? $query->whereHas('equipment', fn ($equipment) => $equipment->where('owner_id', $request->user()->id))
                        : $query->where('user_id', $request->user()->id);
                })->latest()->get();
            $extensions = RentalExtension::with('rental.equipment', 'rental.user')
                ->whereHas('rental', function ($query) use ($owner, $request): void {
                    $owner
                        ? $query->whereHas('equipment', fn ($equipment) => $equipment->where('owner_id', $request->user()->id))
                        : $query->where('user_id', $request->user()->id);
                })->latest()->get();

            return view('pages.front.live-workspace', [
                'title' => __($page['title']), 'slug' => $slug, 'owner' => $owner,
                'reservations' => $reservations, 'rentals' => $rentals,
                'payments' => $payments, 'contracts' => $contracts, 'extensions' => $extensions,
            ]);
        }

        if (in_array($slug, ['my-dashboard', 'owner-dashboard'], true)) {
            $owner = $request->user()->isOwner();
            $reservationQuery = Reservation::query()
                ->when($owner,
                    fn ($query) => $query->whereHas('equipment', fn ($equipment) => $equipment->where('owner_id', $request->user()->id)),
                    fn ($query) => $query->where('user_id', $request->user()->id));
            $pendingReservations = (clone $reservationQuery)->where('status', 'pending')->count();
            $reservationCount = (clone $reservationQuery)->count();
            $reservations = (clone $reservationQuery)->with(['equipment', 'user'])->latest()->take(6)->get();
            $rentals = Rental::query()->when($owner,
                fn ($query) => $query->whereHas('equipment', fn ($equipment) => $equipment->where('owner_id', $request->user()->id)),
                fn ($query) => $query->where('user_id', $request->user()->id));
            $equipmentCount = $owner ? Equipment::where('owner_id', $request->user()->id)->count() : null;
            $revenue = Payment::query()->where('status', 'paid')->whereHas('reservation', function ($query) use ($owner, $request): void {
                $owner
                    ? $query->whereHas('equipment', fn ($equipment) => $equipment->where('owner_id', $request->user()->id))
                    : $query->where('user_id', $request->user()->id);
            })->sum('amount');

            return view('pages.front.live-dashboard', [
                'title' => __($page['title']), 'owner' => $owner, 'reservations' => $reservations,
                'pendingReservations' => $pendingReservations, 'rentals' => $rentals,
                'reservationCount' => $reservationCount,
                'activeRentals' => (clone $rentals)->where('status', 'active')->count(),
                'equipmentCount' => $equipmentCount, 'revenue' => $revenue,
            ]);
        }

        if ($request->user()?->isBuyer() && in_array($slug, [
            'my-reservations',
            'buyer-reservation-detail',
            'my-rentals',
            'buyer-rental-detail',
            'my-contract',
            'my-extensions',
            'my-payments',
            'my-notifications',
        ], true)) {
            return view('pages.front.buyer-workspace', [
                'title' => __($page['title']),
                'user' => $request->user(),
                'slug' => $slug,
            ]);
        }

        if ($request->user()?->isOwner() && in_array($slug, [
            'my-equipment',
            'my-publish',
            'my-equipment-detail',
            'my-equipment-edit',
            'my-reservations',
            'my-reservation-detail',
            'my-rentals',
            'my-rental-detail',
            'my-contract',
            'my-extensions',
            'my-inspections',
            'my-calendar',
            'my-earnings',
            'my-maintenance',
            'my-notifications',
        ], true)) {
            return view('pages.front.owner-workspace', [
                'title' => __($page['title']),
                'user' => $request->user(),
                'slug' => $slug,
            ]);
        }

        return view('pages.front.page', [
            'title' => $page['title'],
            'page' => $page,
            'slug' => $slug,
        ]);
    }
}
