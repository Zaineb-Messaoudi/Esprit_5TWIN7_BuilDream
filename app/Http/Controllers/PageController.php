<?php

namespace App\Http\Controllers;

use App\Support\FrontDemo;
use App\Models\Rental;
use App\Models\Reservation;
use App\Models\Payment;
use App\Models\RentalContract;
use App\Models\RentalExtension;
use App\Models\Equipment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

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

    public function __invoke(Request $request): View
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
