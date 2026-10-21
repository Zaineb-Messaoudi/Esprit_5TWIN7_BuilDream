<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Services\ReservationPaymentService;

class OverdueBalanceReminderNotification extends BaseNotification
{
    public function __construct(
        public Invoice $invoice,
        public string $stage = 'overdue',
    ) {
        $this->recipientId = $invoice->reservation->user_id;
    }

    public function title(): string
    {
        return match ($this->stage) {
            'three_days' => __('Balance due in 3 days'),
            'due_today' => __('Balance due today'),
            default => __('Overdue balance reminder'),
        };
    }

    public function body(): string
    {
        $reservation = $this->invoice->reservation;
        $amount = app(ReservationPaymentService::class)->remainingAmount($reservation);
        $dueDate = $reservation->start_date->copy()->subDays(7)->format('M j, Y');

        return match ($this->stage) {
            'three_days' => __('Your remaining balance of :amount TND for :equipment is due in 3 days, on :date.', [
                'amount' => number_format($amount, 2),
                'equipment' => $reservation->equipment->name,
                'date' => $dueDate,
            ]),
            'due_today' => __('Your remaining balance of :amount TND for :equipment is due today (:date). Please complete your payment.', [
                'amount' => number_format($amount, 2),
                'equipment' => $reservation->equipment->name,
                'date' => $dueDate,
            ]),
            default => __('Your remaining balance of :amount TND for :equipment was due on :date.', [
                'amount' => number_format($amount, 2),
                'equipment' => $reservation->equipment->name,
                'date' => $dueDate,
            ]),
        };
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
            'equipment' => $this->invoice->reservation->equipment_id,
            'reservation' => $this->invoice->reservation_id,
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
