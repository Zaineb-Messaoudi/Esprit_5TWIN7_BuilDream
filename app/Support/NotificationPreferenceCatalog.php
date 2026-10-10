<?php

namespace App\Support;

/**
 * Single source of truth for every email notification type SolarShare can send.
 *
 * Each entry maps a stable preference key (also returned by
 * App\Notifications\*::emailPreferenceKey()) to a human label and description.
 * The settings form iterates this list, the User model consults it through
 * wantsEmail(), and the BaseNotification consults the same key.
 */
class NotificationPreferenceCatalog
{
    /**
     * @return array<int, array{key: string, name: string, description: string}>
     */
    public static function all(): array
    {
        return [
            [
                'key' => 'reservation_created',
                'name' => __('New reservation request'),
                'description' => __('Someone requests your equipment. You decide whether to approve.'),
            ],
            [
                'key' => 'reservation_approved',
                'name' => __('Reservation approved'),
                'description' => __('Your request was accepted. Time to record your payment.'),
            ],
            [
                'key' => 'reservation_rejected',
                'name' => __('Reservation declined'),
                'description' => __('Your request was declined by the owner.'),
            ],
            [
                'key' => 'payment_received',
                'name' => __('Payment received'),
                'description' => __('A renter paid for a reservation on your equipment.'),
            ],
            [
                'key' => 'rental_started',
                'name' => __('Rental started'),
                'description' => __('A rental begins, for both renter and owner.'),
            ],
            [
                'key' => 'extension_requested',
                'name' => __('Extension request'),
                'description' => __('A renter wants to extend a rental on your equipment.'),
            ],
            [
                'key' => 'extension_approved',
                'name' => __('Extension approved'),
                'description' => __('Your extension request was accepted.'),
            ],
            [
                'key' => 'extension_rejected',
                'name' => __('Extension declined'),
                'description' => __('Your extension request was declined.'),
            ],
            [
                'key' => 'maintenance_created',
                'name' => __('Maintenance started'),
                'description' => __('Your equipment entered maintenance.'),
            ],
            [
                'key' => 'maintenance_completed',
                'name' => __('Maintenance completed'),
                'description' => __('Your equipment is available again.'),
            ],
            [
                'key' => 'equipment_returned',
                'name' => __('Equipment returned'),
                'description' => __('A rental ended and the inspection report is ready.'),
            ],
        ];
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return array_column(static::all(), 'key');
    }
}
