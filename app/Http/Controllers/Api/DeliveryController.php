<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreDeliveryRequest;
use App\Http\Requests\Api\UpdateDeliveryStatusRequest;
use App\Models\Delivery;
use App\Models\Rental;
use App\Services\DeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Delivery tracking API controller.
 */
class DeliveryController
{
    public function __construct(private readonly DeliveryService $service) {}

    /**
     * Get all deliveries for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $status = $request->get('status');
        $deliveries = $this->service->getUserDeliveries($user, $status, $request->integer('per_page', 20));

        return response()->json($deliveries);
    }

    /**
     * Get active deliveries for the authenticated user.
     */
    public function active(Request $request): JsonResponse
    {
        $user = $request->user();
        $deliveries = $this->service->getActiveDeliveries($request->user());

        return response()->json($deliveries);
    }

    /**
     * Get delivery statistics for the authenticated user.
     */
    public function stats(Request $request): JsonResponse
    {
        $stats = $this->service->getUserDeliveryStats($request->user());

        return response()->json($stats);
    }

    /**
     * Store a new delivery for a rental.
     */
    public function store(StoreDeliveryRequest $request, Rental $rental): JsonResponse
    {
        $user = $request->user();

        // Verify user is involved in rental
        abort_unless(
            in_array($user->id, [$rental->user_id, $rental->equipment->owner_id]),
            403,
            'Only rental participants can create deliveries.'
        );

        $delivery = $this->service->createDelivery($rental, $request->validated());

        return response()->json([
            'message' => 'Delivery created successfully.',
            'delivery' => $delivery->load(['equipment', 'rental', 'updates']),
        ], 201);
    }

    /**
     * Show a single delivery.
     */
    public function show(Delivery $delivery): JsonResponse
    {
        $user = request()->user();

        // Verify user is involved
        abort_unless(
            in_array($user->id, [$delivery->owner_id, $delivery->renter_id]),
            403
        );

        return response()->json($delivery->load(['equipment', 'rental', 'owner', 'renter', 'updates.updatedBy']));
    }

    /**
     * Update delivery status with proof.
     */
    public function updateStatus(UpdateDeliveryStatusRequest $request, Delivery $delivery): JsonResponse
    {
        $user = $request->user();

        // Verify user is involved
        abort_unless(
            in_array($user->id, [$delivery->owner_id, $delivery->renter_id]),
            403
        );

        $delivery = $this->service->updateStatusWithProof(
            $delivery,
            $request->validated('status'),
            $user,
            $request->validated()
        );

        return response()->json([
            'message' => 'Delivery status updated.',
            'delivery' => $delivery->load(['equipment', 'rental', 'updates.updatedBy']),
        ]);
    }

    /**
     * Schedule a pickup or dropoff.
     */
    public function schedule(Request $request, Delivery $delivery): JsonResponse
    {
        $user = $request->user();

        // Verify user is involved
        abort_unless(
            in_array($user->id, [$delivery->owner_id, $delivery->renter_id]),
            403
        );

        $validated = $request->validate([
            'type' => ['required', 'in:pickup,dropoff'],
            'scheduled_at' => ['required', 'date', 'after:now'],
        ]);

        $delivery = $this->service->schedule($delivery, $validated['type'], $validated['scheduled_at']);

        return response()->json([
            'message' => ucfirst($validated['type']).' scheduled successfully.',
            'delivery' => $delivery->load('updates'),
        ]);
    }

    /**
     * Get deliveries needing attention (admin).
     */
    public function needingAttention(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('admin-only'), 403);

        $deliveries = $this->service->getDeliveriesNeedingAttention()
            ->paginate(20);

        return response()->json($deliveries);
    }

    /**
     * Auto-cancel stale deliveries (admin).
     */
    public function autoCancel(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('admin-only'), 403);

        $count = $this->service->autoCancelStale();

        return response()->json([
            'message' => "Auto-cancelled {$count} stale deliveries.",
            'count' => $count,
        ]);
    }
}
