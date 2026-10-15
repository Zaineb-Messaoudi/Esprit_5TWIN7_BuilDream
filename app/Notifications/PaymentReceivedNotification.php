<?php

namespace App\Notifications;

use App\Models\Payment;

/**
 * Fired when an administrator marks a payment as paid.
 * Delivered to the equipment owner (they receive the money).
 */
class PaymentReceivedNotification extends BaseNotification
{
    public function __construct(
        public Payment $payment,
    ) {
        // The recipient is the equipment owner. The listener loads the
        // reservation + equipment relations before constructing this so
        // the constructor can resolve the owner id without an extra query.
        $this->recipientId = $payment->reservation->equipment->owner_id;
    }

    public function title(): string
    {
        return __('Payment received');
    }

    public function body(): string
    {
        return __(':amount TND received for :equipment from :name', [
            'amount' => number_format((float) $this->payment->amount, 2),
            'equipment' => $this->payment->reservation->equipment->name,
            'name' => $this->payment->reservation->user->name,
        ]);
    }

    public function icon(): string
    {
        return 'wallet';
    }

    public function color(): string
    {
        return 'success';
    }

    public function actionUrl(): ?string
    {
        $rental = $this->payment->reservation->rental;

        if (! $rental) {
            return null;
        }

        return route('front.my-rental-detail', $rental->reference, absolute: false);
    }

    public function actionLabel(): ?string
    {
        return __('View rental');
    }

    public function emailPreferenceKey(): string
    {
        return 'payment_received';
    }
}
