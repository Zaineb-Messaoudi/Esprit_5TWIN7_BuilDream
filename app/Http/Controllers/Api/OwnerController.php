<?php

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use App\Models\Rental;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Owner workspace exposed over the API.
 *
 * All queries are scoped to equipment owned by the authenticated user.
 */
class OwnerController
{
    public function reservations(Request $request): JsonResponse
    {
        $reservations = Reservation::query()
            ->whereHas('equipment', fn ($q) => $q->where('owner_id', $request->user()->id))
            ->with(['equipment.category', 'payments', 'invoice', 'rental.contract', 'rental.extensions'])
            ->latest()
            ->paginate(20)
            ->through(fn (Reservation $r) => $this->transformReservation($r));

        return response()->json($reservations);
    }

    public function decideReservation(Request $request, Reservation $reservation): JsonResponse
    {
        abort_unless($reservation->equipment->owner_id === $request->user()->id, 403);

        $data = $request->validate([
            'decision' => ['required', 'in:confirm,reject'],
        ]);

        $reservation->update(['status' => $data['decision'] === 'confirm' ? 'confirmed' : 'cancelled']);

        return response()->json([
            'ok' => true,
            'reservation' => $this->transformReservation($reservation),
        ]);
    }

    public function rentals(Request $request): JsonResponse
    {
        $rentals = Rental::query()
            ->whereHas('equipment', fn ($q) => $q->where('owner_id', $request->user()->id))
            ->with(['equipment.category', 'contract', 'extensions', 'reservation.payments', 'reservation.invoice'])
            ->latest()
            ->paginate(20)
            ->through(fn (Rental $r) => $this->transformRental($r));

        return response()->json($rentals);
    }

    public function showRental(Rental $rental): JsonResponse
    {
        abort_unless($rental->equipment->owner_id === request()->user()->id, 403);
        $rental->load(['equipment.category', 'contract', 'extensions', 'reservation.payments', 'reservation.invoice', 'reservation.equipment.category']);

        return response()->json($this->transformRental($rental));
    }

    public function earnings(Request $request): JsonResponse
    {
        $ownerId = $request->user()->id;

        $paidPayments = Payment::query()
            ->whereHas('reservation.equipment', fn ($q) => $q->where('owner_id', $ownerId))
            ->where('status', 'paid')
            ->sum('amount');

        $thisMonth = Payment::query()
            ->whereHas('reservation.equipment', fn ($q) => $q->where('owner_id', $ownerId))
            ->where('status', 'paid')
            ->whereMonth('payment_date', now()->month)
            ->sum('amount');

        $pendingPayments = Payment::query()
            ->whereHas('reservation.equipment', fn ($q) => $q->where('owner_id', $ownerId))
            ->where('status', 'pending')
            ->sum('amount');

        return response()->json([
            'total_earnings' => (float) $paidPayments,
            'this_month' => (float) $thisMonth,
            'pending' => (float) $pendingPayments,
            'currency' => 'TND',
        ]);
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

    private function transformReservation(Reservation $r): array
    {
        return [
            'id' => $r->id,
            'reference' => $r->reference,
            'status' => $r->status,
            'equipment' => ['id' => $r->equipment->id, 'name' => $r->equipment->name],
            'renter' => ['id' => $r->user->id, 'name' => $r->user->name],
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
            'renter' => ['id' => $r->user->id, 'name' => $r->user->name],
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
