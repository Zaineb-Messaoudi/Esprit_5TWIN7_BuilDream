<?php

namespace App\Console;

use App\Enums\DepositStatus;
use App\Enums\RentalStatus;
use App\Enums\ReservationStatus;
use App\Models\Maintenance;
use App\Models\Reservation;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Auto-cancel pending reservations older than 24 hours
        $schedule->call(function () {
            $cancelled = Reservation::query()
                ->where('status', ReservationStatus::PENDING)
                ->where('created_at', '<', now()->subDay())
                ->update(['status' => ReservationStatus::CANCELLED]);

            if ($cancelled > 0) {
                \Log::info("Auto-cancelled {$cancelled} stale reservations");
            }
        })->dailyAt('03:00')->description('Auto-cancel stale pending reservations');

        // Send payment reminder for pending payments on confirmed reservations
        $schedule->call(function () {
            $payments = \App\Models\Payment::query()
                ->where('status', 'pending')
                ->whereHas('reservation', function ($q) {
                    $q->where('status', \App\Enums\ReservationStatus::CONFIRMED)
                        ->where('start_date', '<=', now()->addDays(2));
                })
                ->with('reservation.equipment', 'reservation.user')
                ->get();

            foreach ($payments as $payment) {
                \App\Notifications\PaymentReminderNotification::send(
                    $payment->reservation->user,
                    new \App\Notifications\PaymentReminderNotification($payment)
                );
            }

            if ($payments->count() > 0) {
                \Log::info("Sent {$payments->count()} payment reminders");
            }
        })->dailyAt('09:00')->description('Send payment reminders for upcoming rentals');

        // Auto-release deposits for completed rentals (7 days after return)
        $schedule->call(function () {
            $released = \App\Models\RentalContract::query()
                ->where('deposit_status', DepositStatus::HELD)
                ->whereHas('rental', function ($q) {
                    $q->where('status', RentalStatus::COMPLETED)
                        ->where('actual_end_date', '<=', now()->subWeek());
                })
                ->update(['deposit_status' => DepositStatus::RELEASED]);

            if ($released > 0) {
                \Log::info("Auto-released {$released} rental deposits");
            }
        })->dailyAt('04:00')->description('Auto-release deposits for completed rentals');

        // Forfeit deposits for damaged returns (if inspection shows damage and not yet forfeited)
        $schedule->call(function () {
            $forfeited = \App\Models\RentalContract::query()
                ->where('deposit_status', DepositStatus::HELD)
                ->whereHas('rental.inspection', function ($q) {
                    $q->where('damage_detected', true);
                })
                ->whereHas('rental', function ($q) {
                    $q->where('status', RentalStatus::COMPLETED);
                })
                ->update(['deposit_status' => DepositStatus::FORFEITED]);

            if ($forfeited > 0) {
                \Log::info("Auto-forfeited {$forfeited} deposits due to damage");
            }
        })->dailyAt('05:00')->description('Auto-forfeit deposits for damaged equipment');

        // Send maintenance reminders (equipment due for maintenance)
        $schedule->call(function () {
            $dueMaintenance = Maintenance::query()
                ->where('status', 'scheduled')
                ->where('start_date', '<=', now()->addDays(3))
                ->with('equipment.owner')
                ->get();

            foreach ($dueMaintenance as $maintenance) {
                \App\Notifications\MaintenanceReminderNotification::send(
                    $maintenance->equipment->owner,
                    new \App\Notifications\MaintenanceReminderNotification($maintenance)
                );
            }

            if ($dueMaintenance->count() > 0) {
                \Log::info("Sent {$dueMaintenance->count()} maintenance reminders");
            }
        })->dailyAt('08:00')->description('Send upcoming maintenance reminders');

        // Clean up old notifications (>90 days)
        $schedule->call(function () {
            $deleted = \App\Models\Notification::query()
                ->where('created_at', '<', now()->subDays(90))
                ->delete();

            if ($deleted > 0) {
                \Log::info("Cleaned up {$deleted} old notifications");
            }
        })->weekly()->description('Clean up old notifications');

        // Heartbeat for monitoring
        $schedule->call(function () {
            \Log::debug('Scheduler heartbeat');
        })->everyMinute()->description('Scheduler heartbeat');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
