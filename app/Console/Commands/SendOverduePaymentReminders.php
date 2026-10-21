<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Notifications\OverdueBalanceReminderNotification;
use App\Services\ReservationPaymentService;
use Illuminate\Console\Command;

class SendOverduePaymentReminders extends Command
{
    protected $signature = 'payments:send-overdue-reminders';

    protected $description = 'Send scheduled balance reminders before and after the due date';

    public function handle(ReservationPaymentService $paymentPlan): int
    {
        $sent = [
            'three_days' => 0,
            'due_today' => 0,
            'overdue' => 0,
        ];

        Invoice::query()
            ->where('status', 'partially_paid')
            ->whereHas('reservation', fn ($query) => $query->where('status', 'confirmed'))
            ->with(['reservation.equipment', 'reservation.user', 'reservation.payments'])
            ->chunkById(100, function ($invoices) use ($paymentPlan, &$sent): void {
                foreach ($invoices as $invoice) {
                    $reservation = $invoice->reservation;
                    if ($paymentPlan->remainingAmount($reservation) <= 0) {
                        continue;
                    }

                    $daysUntilStart = (int) today()->startOfDay()->diffInDays(
                        $reservation->start_date->copy()->startOfDay(),
                        false
                    );
                    $daysUntilDue = $daysUntilStart - 7;
                    $stage = match (true) {
                        $daysUntilDue === 3 && ! $invoice->balance_reminder_three_days_sent_at => 'three_days',
                        $daysUntilDue === 0 && ! $invoice->balance_reminder_due_today_sent_at => 'due_today',
                        $daysUntilDue < 0 && ! $invoice->balance_reminder_sent_at => 'overdue',
                        default => null,
                    };

                    if ($stage === null) {
                        continue;
                    }

                    $reservation->user->notify(new OverdueBalanceReminderNotification($invoice, $stage));
                    $timestamp = now();
                    $invoice->update(match ($stage) {
                        'three_days' => ['balance_reminder_three_days_sent_at' => $timestamp],
                        'due_today' => ['balance_reminder_due_today_sent_at' => $timestamp],
                        default => ['balance_reminder_sent_at' => $timestamp],
                    });
                    $sent[$stage]++;
                }
            });

        $this->info(sprintf(
            'Sent %d balance reminder(s) (3 days before: %d, due today: %d, overdue: %d).',
            array_sum($sent),
            $sent['three_days'],
            $sent['due_today'],
            $sent['overdue'],
        ));

        return self::SUCCESS;
    }
}
