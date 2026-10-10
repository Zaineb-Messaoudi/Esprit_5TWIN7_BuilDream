<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\DeliveryUpdate;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DeliveryService
{
    /**
     * Create a delivery for a rental.
     */
    public function createDelivery(
        Rental $rental,
        array $data
    ): Delivery {
        return DB::transaction(function () use ($rental, $data) {
            $delivery = Delivery::create([
                'rental_id' => $rental->id,
                'equipment_id' => $rental->equipment_id,
                'owner_id' => $rental->equipment->owner_id,
                'renter_id' => $rental->user_id,
                'type' => $data['type'] ?? 'both',
                'status' => 'scheduled',
                'carrier' => $data['carrier'] ?? 'self',
                'pickup_address' => $data['pickup_address'] ?? [],
                'dropoff_address' => $data['dropoff_address'] ?? [],
                'scheduled_pickup_at' => $data['scheduled_pickup_at'] ?? null,
                'scheduled_dropoff_at' => $data['scheduled_dropoff_at'] ?? null,
                'dimensions' => $data['dimensions'] ?? [],
                'special_instructions' => $data['special_instructions'] ?? null,
                'delivery_fee' => $data['delivery_fee'] ?? 0,
                'fee_paid_by' => $data['fee_paid_by'] ?? 'split',
            ]);

            // Generate tracking number
            $delivery->update([
                'tracking_number' => 'DEL-'.strtoupper(Str::random(10)),
            ]);

            // Create initial status update
            $this->createUpdate($delivery, 'scheduled', [
                'notes' => 'Delivery scheduled',
                'updated_by' => auth()->id(),
            ]);

            Log::info("Delivery created: {$delivery->tracking_number} for rental {$rental->reference}");

            return $delivery;
        });
    }

    /**
     * Create a status update for a delivery.
     */
    public function createUpdate(
        Delivery $delivery,
        string $status,
        array $data = []
    ): DeliveryUpdate {
        $update = DeliveryUpdate::create([
            'delivery_id' => $delivery->id,
            'status' => $status,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'location_name' => $data['location_name'] ?? null,
            'notes' => $data['notes'] ?? null,
            'updated_by' => $data['updated_by'] ?? auth()->id(),
        ]);

        // Update delivery status
        $delivery->update(['status' => $status]);

        // Update timestamps based on status
        if ($status === 'in_transit' && ! $delivery->actual_pickup_at) {
            $delivery->update(['actual_pickup_at' => now()]);
        }
        if ($status === 'completed' && ! $delivery->actual_dropoff_at) {
            $delivery->update(['actual_dropoff_at' => now()]);
        }

        return $update;
    }

    /**
     * Get deliveries for a user (owner or renter).
     */
    public function getUserDeliveries(User $user, ?string $status = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = Delivery::query()
            ->where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                    ->orWhere('renter_id', $user->id);
            })
            ->with(['equipment', 'rental', 'owner', 'renter', 'updates' => fn ($q) => $q->latest()->limit(3)])
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get active deliveries for a user.
     */
    public function getActiveDeliveries(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return Delivery::active()
            ->where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                    ->orWhere('renter_id', $user->id);
            })
            ->with(['equipment', 'rental', 'updates' => fn ($q) => $q->latest()->limit(1)])
            ->get();
    }

    /**
     * Get deliveries needing attention (issues or overdue).
     */
    public function getDeliveriesNeedingAttention(): \Illuminate\Database\Eloquent\Collection
    {
        return Delivery::needingAttention()
            ->with(['equipment', 'rental', 'owner', 'renter'])
            ->get();
    }

    /**
     * Update delivery status with proof.
     */
    public function updateStatusWithProof(
        Delivery $delivery,
        string $status,
        User $user,
        array $data
    ): Delivery {
        // Verify user is involved
        if (! in_array($user->id, [$delivery->owner_id, $delivery->renter_id])) {
            throw new \Exception('Unauthorized to update this delivery');
        }

        $validStatuses = ['in_transit', 'arrived', 'completed', 'cancelled', 'issue'];
        abort_unless(in_array($status, $validStatuses), 400, 'Invalid status');

        $proofUrl = $data['proof_url'] ?? null;
        $proofType = $data['proof_type'] ?? null; // 'pickup' or 'dropoff'

        $updateData = [
            'notes' => $data['notes'] ?? null,
            'updated_by' => $user->id,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'location_name' => $data['location_name'] ?? null,
        ];

        $this->createUpdate($delivery, $status, $updateData);

        // Handle proof upload
        if ($proofUrl && $proofType) {
            if ($proofType === 'pickup') {
                $delivery->update(['proof_of_pickup_url' => $proofUrl]);
            } elseif ($proofType === 'dropoff') {
                $delivery->update(['proof_of_dropoff_url' => $proofUrl]);
            }
        }

        return $delivery;
    }

    /**
     * Schedule a pickup or dropoff.
     */
    public function schedule(
        Delivery $delivery,
        string $type, // 'pickup' or 'dropoff'
        \DateTime $scheduledAt
    ): Delivery {
        $field = $type === 'pickup' ? 'scheduled_pickup_at' : 'scheduled_dropoff_at';

        $delivery->update([
            $field => $scheduledAt,
            'status' => 'scheduled',
        ]);

        $this->createUpdate($delivery, 'scheduled', [
            'notes' => ucfirst($type).' scheduled for '.$scheduledAt->format('M j, Y H:i'),
            'updated_by' => auth()->id(),
        ]);

        return $delivery;
    }

    /**
     * Get delivery statistics for a user.
     */
    public function getUserDeliveryStats(User $user): array
    {
        $deliveries = Delivery::where(function ($q) use ($user) {
            $q->where('owner_id', $user->id)
                ->orWhere('renter_id', $user->id);
        });

        return [
            'total' => $deliveries->count(),
            'completed' => $deliveries->where('status', 'completed')->count(),
            'active' => $deliveries->whereIn('status', ['scheduled', 'in_transit'])->count(),
            'issues' => $deliveries->where('status', 'issue')->count(),
            'total_fees_paid' => $deliveries->sum('delivery_fee'),
            'avg_delivery_time' => $this->calculateAvgDeliveryTime($user),
        ];
    }

    /**
     * Calculate average delivery time for a user.
     */
    private function calculateAvgDeliveryTime(User $user): ?string
    {
        $completed = Delivery::query()
            ->where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                    ->orWhere('renter_id', $user->id);
            })
            ->where('status', 'completed')
            ->whereNotNull('actual_pickup_at')
            ->whereNotNull('actual_dropoff_at')
            ->get();

        if ($completed->isEmpty()) {
            return null;
        }

        $totalMinutes = $completed->sum(function ($d) {
            return $d->actual_pickup_at->diffInMinutes($d->actual_dropoff_at);
        });

        $avgMinutes = round($totalMinutes / $completed->count());

        $hours = floor($avgMinutes / 60);
        $minutes = $avgMinutes % 60;

        return $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";
    }

    /**
     * Auto-cancel stale scheduled deliveries.
     */
    public function autoCancelStale(): int
    {
        $cancelled = Delivery::query()
            ->where('status', 'scheduled')
            ->where(function ($q) {
                $q->where('scheduled_pickup_at', '<', now()->subHours(2))
                    ->orWhere('scheduled_dropoff_at', '<', now()->subHours(2));
            })
            ->update(['status' => 'cancelled']);

        if ($cancelled > 0) {
            Log::info("Auto-cancelled {$cancelled} stale deliveries");
        }

        return $cancelled;
    }
}
