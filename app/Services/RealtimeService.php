<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

/**
 * Real-time WebSocket service for managing connections and broadcasting.
 */
class RealtimeService
{
    /**
     * Broadcast to a user's private channel.
     */
    public function broadcastToUser(int $userId, string $event, array $data): void
    {
        broadcast(new \App\Events\UserNotification(
            userId: $userId,
            event: $event,
            data: $data
        ))->toOthers();
    }

    /**
     * Broadcast to a rental's channel (for chat between renter and owner).
     */
    public function broadcastToRental(int $rentalId, string $event, array $data): void
    {
        broadcast(new \App\Events\RentalChannelEvent(
            rentalId: $rentalId,
            event: $event,
            data: $data
        ))->toOthers();
    }

    /**
     * Broadcast to a delivery's channel (for live tracking).
     */
    public function broadcastToDelivery(int $deliveryId, string $event, array $data): void
    {
        broadcast(new \App\Events\DeliveryChannelEvent(
            deliveryId: $deliveryId,
            event: $event,
            data: $data
        ))->toOthers();
    }

    /**
     * Broadcast to a global admin channel.
     */
    public function broadcastToAdmins(string $event, array $data): void
    {
        broadcast(new \App\Events\AdminChannelEvent(
            event: $event,
            data: $data
        ))->toOthers();
    }

    /**
     * Broadcast equipment availability update.
     */
    public function broadcastEquipmentUpdate(int $equipmentId, string $event, array $data): void
    {
        broadcast(new \App\Events\EquipmentChannelEvent(
            equipmentId: $equipmentId,
            event: $event,
            data: $data
        ))->toOthers();
    }

    /**
     * Get online users count (via Redis presence).
     */
    public function getOnlineUsersCount(): int
    {
        $keys = Redis::keys('reverb:presence:*:members');

        return count($keys);
    }

    /**
     * Check if a user is online.
     */
    public function isUserOnline(int $userId): bool
    {
        return Redis::exists("reverb:presence:private-user.{$userId}:members");
    }

    /**
     * Get user's active connections count.
     */
    public function getUserConnectionsCount(int $userId): int
    {
        return (int) Redis::scard("reverb:presence:private-user.{$userId}:members");
    }

    /**
     * Broadcast typing indicator.
     */
    public function broadcastTyping(int $rentalId, int $userId, bool $isTyping): void
    {
        $this->broadcastToRental($rentalId, 'typing', [
            'user_id' => $userId,
            'is_typing' => $isTyping,
        ]);
    }

    /**
     * Broadcast message read receipt.
     */
    public function broadcastReadReceipt(int $rentalId, int $messageId, int $userId): void
    {
        $this->broadcastToRental($rentalId, 'message.read', [
            'message_id' => $messageId,
            'read_by' => $userId,
            'read_at' => now()->toISOString(),
        ]);
    }
}
