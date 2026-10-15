<?php

namespace App\Http\Controllers\Api;

use App\Models\Equipment;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Marketplace (catalogue + booking) exposed over the API.
 *
 * Read-only catalogue endpoints are public (no auth:sanctum).
 * Booking endpoints require authentication.
 */
class MarketplaceController
{
    public function index(Request $request): JsonResponse
    {
        $query = Equipment::query()
            ->where('approval_status', 'published')
            ->where('status', 'available')
            ->with('category', 'owner:id,name');

        // Filters
        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }
        if ($request->filled('min_price')) {
            $query->where('price_per_day', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price_per_day', '<=', $request->max_price);
        }
        if ($request->filled('location')) {
            $query->where('location', 'like', '%'.$request->location.'%');
        }
        if ($request->filled('power_min')) {
            $query->where('power_watts', '>=', $request->power_min);
        }
        if ($request->filled('capacity_min')) {
            $query->where('capacity_wh', '>=', $request->capacity_min);
        }

        $equipment = $query->latest()->paginate(20)
            ->through(fn (Equipment $e) => [
                'id' => $e->id,
                'name' => $e->name,
                'slug' => $e->slug,
                'category' => $e->category ? ['id' => $e->category->id, 'name' => $e->category->name, 'slug' => $e->category->slug] : null,
                'price_per_day' => (float) $e->price_per_day,
                'currency' => 'TND',
                'location' => $e->location,
                'condition' => $e->condition,
                'power_watts' => $e->power_watts,
                'capacity_wh' => $e->capacity_wh,
                'image_url' => $e->image_url,
                'owner' => ['id' => $e->owner->id, 'name' => $e->owner->name],
                'status' => $e->status,
            ]);

        return response()->json($equipment);
    }

    public function show(Equipment $equipment): JsonResponse
    {
        $equipment->load('category', 'owner:id,name');

        return response()->json([
            'id' => $equipment->id,
            'name' => $equipment->name,
            'slug' => $equipment->slug,
            'description' => $equipment->description,
            'category' => $equipment->category ? ['id' => $equipment->category->id, 'name' => $equipment->category->name, 'slug' => $equipment->category->slug] : null,
            'price_per_day' => (float) $equipment->price_per_day,
            'currency' => 'TND',
            'location' => $equipment->location,
            'condition' => $equipment->condition,
            'power_watts' => $equipment->power_watts,
            'capacity_wh' => $equipment->capacity_wh,
            'image_url' => $equipment->image_url,
            'owner' => ['id' => $equipment->owner->id, 'name' => $equipment->owner->name],
            'status' => $equipment->status,
            'approval_status' => $equipment->approval_status,
        ]);
    }

    public function availability(Request $request, Equipment $equipment): JsonResponse
    {
        $start = $request->get('start') ? Carbon::parse($request->get('start')) : now()->startOfDay();
        $end = $request->get('end') ? Carbon::parse($request->get('end')) : $start->copy()->addDays(30);

        $reservations = Reservation::query()
            ->where('equipment_id', $equipment->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($qq) use ($start, $end) {
                        $qq->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            })
            ->get(['start_date', 'end_date', 'status', 'reference']);

        $rentals = $equipment->rentals()
            ->whereIn('status', ['active', 'pending'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($qq) use ($start, $end) {
                        $qq->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            })
            ->get(['start_date', 'end_date', 'status', 'reference']);

        return response()->json([
            'equipment_id' => $equipment->id,
            'availability' => $reservations->isEmpty() && $rentals->isEmpty(),
            'reserved_periods' => $reservations->map(fn ($r) => [
                'start' => $r->start_date->toDateString(),
                'end' => $r->end_date->toDateString(),
                'status' => $r->status,
                'reference' => $r->reference,
                'type' => 'reservation',
            ]),
            'rental_periods' => $rentals->map(fn ($r) => [
                'start' => $r->start_date->toDateString(),
                'end' => $r->end_date->toDateString(),
                'status' => $r->status->value,
                'reference' => $r->reference,
                'type' => 'rental',
            ]),
        ]);
    }

    public function storeReservation(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->isBuyer(), 403, 'Only buyers can create reservations.');

        $data = $request->validate([
            'equipment_id' => ['required', 'exists:equipment,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $equipment = Equipment::findOrFail($data['equipment_id']);
        abort_unless($equipment->approval_status === 'published' && $equipment->status === 'available', 409, 'Equipment is not available for booking.');

        // Overlap check (mirrors MarketplaceController::storeReservation)
        $overlaps = Reservation::query()
            ->where('equipment_id', $equipment->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(function ($q) use ($data) {
                $q->whereBetween('start_date', [$data['start_date'], $data['end_date']])
                    ->orWhereBetween('end_date', [$data['start_date'], $data['end_date']])
                    ->orWhere(function ($qq) use ($data) {
                        $qq->where('start_date', '<=', $data['start_date'])
                            ->where('end_date', '>=', $data['end_date']);
                    });
            })
            ->exists();

        if ($overlaps) {
            return response()->json(['message' => 'Equipment is already reserved for the selected dates.'], 409);
        }

        $rentalOverlaps = \App\Models\Rental::query()
            ->where('equipment_id', $equipment->id)
            ->whereIn('status', ['pending', 'active'])
            ->where(function ($q) use ($data) {
                $q->whereBetween('start_date', [$data['start_date'], $data['end_date']])
                    ->orWhereBetween('end_date', [$data['start_date'], $data['end_date']])
                    ->orWhere(function ($qq) use ($data) {
                        $qq->where('start_date', '<=', $data['start_date'])
                            ->where('end_date', '>=', $data['end_date']);
                    });
            })
            ->exists();

        if ($rentalOverlaps) {
            return response()->json(['message' => 'Equipment is already rented for the selected dates.'], 409);
        }

        $days = max(1, Carbon::parse($data['start_date'])->diffInDays(Carbon::parse($data['end_date'])) + 1);
        $totalAmount = (float) $equipment->price_per_day * $days;

        $reservation = Reservation::create([
            'reference' => 'RES-'.strtoupper(Str::random(8)),
            'equipment_id' => $equipment->id,
            'user_id' => $user->id,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'total_amount' => $totalAmount,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Reservation created successfully.',
            'reservation' => [
                'id' => $reservation->id,
                'reference' => $reservation->reference,
                'equipment_id' => $equipment->id,
                'equipment_name' => $equipment->name,
                'start_date' => $reservation->start_date->toDateString(),
                'end_date' => $reservation->end_date->toDateString(),
                'total_amount' => $totalAmount,
                'status' => $reservation->status,
            ],
        ], 201);
    }

    public function payReservation(Request $request, Reservation $reservation): JsonResponse
    {
        $user = $request->user();
        abort_unless($reservation->user_id === $user->id, 403);

        $data = $request->validate([
            'payment_method' => ['required', 'in:card,bank_transfer,cash,other'],
        ]);

        $amount = round((float) $reservation->total_amount * 1.19, 2); // +19% VAT
        $reference = 'PAY-'.strtoupper(Str::random(10));

        $payment = \App\Models\Payment::create([
            'reservation_id' => $reservation->id,
            'amount' => $amount,
            'payment_method' => $data['payment_method'],
            'transaction_reference' => $reference,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Payment recorded. Awaiting verification.',
            'payment' => [
                'id' => $payment->id,
                'transaction_reference' => $reference,
                'amount' => $amount,
                'payment_method' => $data['payment_method'],
                'status' => 'pending',
            ],
        ], 201);
    }
}
