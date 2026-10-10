<?php

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use App\Models\Rental;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Buyer (renter) workspace exposed over the API.
 *
 * All queries are scoped to the authenticated user; the owner-side
 * reservations/rentals are deliberately excluded.
 */
class BuyerController
{
    public function reservations(Request $request): JsonResponse
    {
        $reservations = Reservation::query()
            ->where('user_id', $request->user()->id)
            ->with(['equipment.category', 'payments', 'invoice'])
            ->latest()
            ->paginate(20)
            ->through(fn (Reservation $r) => $this->transformReservation($r));

        return response()->json($reservations);
    }

    public function showReservation(Reservation $reservation): JsonResponse
    {
        abort_unless($reservation->user_id === request()->user()->id, 403);
        $reservation->load(['equipment.category', 'payments', 'invoice', 'rental.contract', 'rental.extensions']);

        return response()->json($this->transformReservation($reservation));
    }

    public function rentals(Request $request): JsonResponse
    {
        $rentals = Rental::query()
            ->where('user_id', $request->user()->id)
            ->with(['equipment.category', 'contract', 'extensions', 'reservation.payments'])
            ->latest()
            ->paginate(20)
            ->through(fn (Rental $r) => $this->transformRental($r));

        return response()->json($rentals);
    }

    public function showRental(Rental $rental): JsonResponse
    {
        abort_unless($rental->user_id === request()->user()->id, 403);
        $rental->load(['equipment.category', 'contract', 'extensions', 'reservation.payments', 'reservation.invoice']);

        return response()->json($this->transformRental($rental));
    }

    public function payments(Request $request): JsonResponse
    {
        $payments = Payment::query()
            ->whereHas('reservation', fn ($q) => $q->where('user_id', $request->user()->id))
            ->with('reservation.equipment')
            ->latest()
            ->paginate(20)
            ->through(fn (Payment $p) => [
                'id' => $p->id,
                'transaction_reference' => $p->transaction_reference,
                'amount' => (float) $p->amount,
                'payment_method' => $p->payment_method,
                'status' => $p->status,
                'payment_date' => $p->payment_date?->toIso8601String(),
                'reservation' => ['reference' => $p->reservation->reference, 'equipment' => $p->reservation->equipment->name],
            ]);

        return response()->json($payments);
    }

    public function notifications(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => $notifications->through(fn ($n) => [
                'id' => $n->id,
                'type' => $n->data['type'] ?? null,
                'title' => $n->data['title'] ?? null,
                'body' => $n->data['body'] ?? null,
                'icon' => $n->data['icon'] ?? null,
                'color' => $n->data['color'] ?? null,
                'action_url' => $n->data['action_url'] ?? null,
                'action_label' => $n->data['action_label'] ?? null,
                'read_at' => $n->read_at?->toIso8601String(),
                'created_at' => $n->created_at?->toIso8601String(),
            ]),
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function readNotification(Request $request, string $notification_id): JsonResponse
    {
        $n = $request->user()->notifications()->where('id', $notification_id)->first();
        abort_unless($n, 404);
        $n->markAsRead();

        return response()->json(['ok' => true, 'id' => $n->id]);
    }

    public function clearNotifications(Request $request): JsonResponse
    {
        $request->user()->notifications()->delete();

        return response()->json(['ok' => true, 'cleared' => true]);
    }

    private function transformReservation(Reservation $r): array
    {
        return [
            'id' => $r->id,
            'reference' => $r->reference,
            'status' => $r->status,
            'equipment' => ['id' => $r->equipment->id, 'name' => $r->equipment->name],
            'start_date' => $r->start_date->toDateString(),
            'end_date' => $r->end_date->toDateString(),
            'total_amount' => (float) $r->total_amount,
            'rental' => $r->rental ? ['reference' => $r->rental->reference, 'status' => $r->rental->status->value] : null,
            'invoice' => $r->invoice ? ['invoice_number' => $r->invoice->invoice_number, 'status' => $r->invoice->status] : null,
            'payments' => $r->payments->map(fn (Payment $p) => [
                'transaction_reference' => $p->transaction_reference,
                'amount' => (float) $p->amount,
                'status' => $p->status,
            ]),
        ];
    }

    private function transformRental(Rental $r): array
    {
        return [
            'id' => $r->id,
            'reference' => $r->reference,
            'status' => $r->status->value,
            'equipment' => ['id' => $r->equipment->id, 'name' => $r->equipment->name],
            'start_date' => $r->start_date->toDateString(),
            'end_date' => $r->end_date->toDateString(),
            'total_amount' => (float) $r->total_amount,
            'contract' => $r->contract ? ['contract_number' => $r->contract->contract_number, 'status' => $r->contract->contract_status->value] : null,
            'extensions' => $r->extensions->map(fn ($e) => [
                'new_end_date' => $e->new_end_date->toDateString(),
                'additional_amount' => (float) $e->additional_amount,
                'status' => $e->status->value,
            ]),
        ];
    }
}
