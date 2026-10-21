<?php

namespace App\Http\Controllers\Api;

use App\Events\ReservationCreated;
use App\Models\Equipment;
use App\Models\Reservation;
use App\Services\ReservationAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $dates = $request->validate([
            'start' => ['nullable', 'date'],
            'end' => ['nullable', 'date', 'after_or_equal:start'],
        ]);
        $start = isset($dates['start']) ? Carbon::parse($dates['start']) : now()->startOfDay();
        $end = isset($dates['end']) ? Carbon::parse($dates['end']) : $start->copy()->addDays(30);

        $reservations = Reservation::query()
            ->where('equipment_id', $equipment->id)
            ->where('status', '!=', 'cancelled')
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
            ->whereNotIn('status', [\App\Enums\RentalStatus::CANCELLED->value, \App\Enums\RentalStatus::COMPLETED->value])
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
                'type' => 'reservation',
            ]),
            'rental_periods' => $rentals->map(fn ($r) => [
                'start' => $r->start_date->toDateString(),
                'end' => $r->end_date->toDateString(),
                'status' => $r->status->value,
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
            'start_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ]);

        $reservation = DB::transaction(function () use ($data, $user): Reservation {
            $equipment = Equipment::query()->lockForUpdate()->findOrFail($data['equipment_id']);
            abort_unless(
                $equipment->approval_status === 'published' && $equipment->status === 'available',
                409,
                'Equipment is not available for booking.'
            );
            app(ReservationAvailabilityService::class)->assertAvailable(
                $equipment->id,
                $data['start_date'],
                $data['end_date'],
            );

            $days = max(1, Carbon::parse($data['start_date'])->diffInDays(Carbon::parse($data['end_date'])) + 1);
            $totalAmount = round((float) $equipment->price_per_day * $days, 2);

            return Reservation::create([
                'reference' => 'RES-'.strtoupper(Str::random(8)),
                'equipment_id' => $equipment->id,
                'user_id' => $user->id,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);
        });
        $equipment = $reservation->equipment()->with('owner')->firstOrFail();
        ReservationCreated::dispatch($reservation->load('equipment', 'user'), $equipment->owner);

        return response()->json([
            'message' => 'Reservation created successfully.',
            'reservation' => [
                'id' => $reservation->id,
                'reference' => $reservation->reference,
                'equipment_id' => $equipment->id,
                'equipment_name' => $equipment->name,
                'start_date' => $reservation->start_date->toDateString(),
                'end_date' => $reservation->end_date->toDateString(),
                'total_amount' => (float) $reservation->total_amount,
                'status' => $reservation->status,
            ],
        ], 201);
    }

    public function payReservation(Request $request, Reservation $reservation): JsonResponse
    {
        $user = $request->user();
        abort_unless($reservation->user_id === $user->id, 403);
        abort_unless($reservation->status === 'confirmed', 409, 'Reservation must be approved before payment.');

        $data = $request->validate([
            'payment_method' => ['required', 'in:card,bank_transfer,cash'],
        ]);

        $paymentPlan = app(\App\Services\ReservationPaymentService::class);
        $amount = $paymentPlan->nextPaymentAmount($reservation);
        abort_if($amount <= 0, 409, 'Reservation is already fully paid.');
        abort_if($reservation->payments()->where('status', 'pending')->exists(), 409, 'A payment is already awaiting verification.');
        $paymentStage = $paymentPlan->nextPaymentStage($reservation);
        $dueDate = $paymentPlan->nextPaymentDueDate($reservation);

        if ($data['payment_method'] === 'card' && filled(config('services.stripe.secret'))) {
            $stripeCurrency = strtolower(config('services.stripe.checkout_currency', 'usd'));
            $tndToStripeRate = (float) config('services.stripe.tnd_to_checkout_rate', 0);

            abort_if($tndToStripeRate <= 0, 503, __('Stripe checkout exchange rate is not configured.'));

            $session = app(\App\Services\StripeConnectService::class)->createCheckoutSession(
                round($amount * $tndToStripeRate, 2),
                $stripeCurrency,
                $reservation->reference,
                $reservation->id,
                $user->id,
                route('front.confirmed', ['equipment' => $reservation->equipment_id, 'reservation' => $reservation->id]).'?checkout=success',
                route('front.booking-summary', ['equipment' => $reservation->equipment_id, 'reservation' => $reservation->id]).'?checkout=cancelled'
            );

            $payment = \App\Models\Payment::create([
                'reservation_id' => $reservation->id,
                'amount' => $amount,
                'payment_stage' => $paymentStage,
                'due_date' => $dueDate,
                'payment_date' => now(),
                'payment_method' => 'CARD',
                'transaction_reference' => $session->id,
                'status' => 'pending',
            ]);

            return response()->json([
                'message' => 'Stripe checkout started.',
                'checkout_url' => $session->url,
                'payment' => [
                    'id' => $payment->id,
                    'transaction_reference' => $session->id,
                    'amount' => $amount,
                    'stage' => $paymentStage,
                    'due_date' => $dueDate,
                    'payment_method' => 'card',
                    'status' => 'pending',
                ],
            ], 201);
        }

        abort_if(
            $data['payment_method'] === 'card',
            503,
            __('Card payments are unavailable because Stripe is not configured.')
        );

        $reference = 'PAY-'.strtoupper(Str::random(10));

        $payment = \App\Models\Payment::create([
            'reservation_id' => $reservation->id,
            'amount' => $amount,
            'payment_stage' => $paymentStage,
            'due_date' => $dueDate,
            'payment_date' => now(),
            'payment_method' => strtoupper($data['payment_method']),
            'transaction_reference' => $reference,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Payment recorded. Awaiting verification.',
            'payment' => [
                'id' => $payment->id,
                'transaction_reference' => $reference,
                'amount' => $amount,
                'stage' => $paymentStage,
                'due_date' => $dueDate,
                'payment_method' => $data['payment_method'],
                'status' => 'pending',
            ],
        ], 201);
    }
}
