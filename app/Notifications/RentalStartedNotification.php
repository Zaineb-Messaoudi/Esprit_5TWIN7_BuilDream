<?php

namespace App\Notifications;

use App\Models\Rental;

/**
 * Fired when a rental starts (payment verified, contract generated).
 * Delivered to BOTH the buyer (renter) and the equipment owner.
 *
 * The listener notifies each recipient separately so the broadcast
 * channel and deep link resolve to the right user.
 */
class RentalStartedNotification extends BaseNotification
{
    public function __construct(
        public Rental $rental,
    ) {
        // No fixed recipient: the listener notifies owner and buyer in turn,
        // and broadcastOn() falls back to the notifiable actually receiving it.
    }

    public function title(): string
    {
        return __('Rental started');
    }

    public function body(): string
    {
        return __('Rental :reference has started for :equipment.', [
            'reference' => $this->rental->reference,
            'equipment' => $this->rental->equipment->name,
        ]);
    }

    public function icon(): string
    {
        return 'key';
    }

    public function color(): string
    {
        return 'brand';
    }

    public function actionUrl(): ?string
    {
        return route('front.my-rental-detail', $this->rental->reference, absolute: false);
    }

    public function actionLabel(): ?string
    {
        return __('View rental');
    }

    public function emailPreferenceKey(): string
    {
        return 'rental_started';
    }
}
