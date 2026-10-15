<?php

namespace App\Notifications;

use App\Models\Payment;

/**
 * Fired when a payment is pending and the rental start date is approaching.
 * Delivered to the buyer (renter).
 */
class PaymentReminderNotification extends BaseNotification
{
    public function __construct(
        public Payment $payment,
    ) {
        $this->recipientId = $payment->reservation->user_id;
    }

    public function title(): string
    {
        return __('Payment reminder');
    }

    public function body(): string
    {
        return __('Your payment of :amount TND for :equipment is still pending. Please complete it before your rental starts on :start.', [
            'amount' => number_format((float) $this->payment->amount, 2),
            'equipment' => $this->payment->reservation->equipment->name,
            'start' => $this->payment->reservation->start_date->format('M j, Y'),
        ]);
    }

    public function icon(): string
    {
        return 'clock';
    }

    public function color(): string
    {
        return 'warning';
    }

    public function actionUrl(): ?string
    {
        return route('front.booking-summary', [
            'equipment' => $this->payment->reservation->equipment_id,
            'reservation' => $this->payment->reservation->id,
        ], absolute: false);
    }

    public function actionLabel(): ?string
    {
        return __('Complete payment');
    }

    public function emailPreferenceKey(): string
    {
        return 'payment_reminder';
    }
}
