<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Equipment;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationPaymentService;
use App\Services\StripeConnectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ReservationInstallmentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_reservation_is_paid_in_deposit_and_balance_without_creating_rental_early(): void
    {
        config([
            'services.stripe.secret' => 'sk_test_placeholder',
            'services.stripe.tnd_to_checkout_rate' => 0.32,
        ]);

        $buyer = User::factory()->create([
            'role' => UserRole::BUYER,
            'role_setup_completed' => true,
        ]);
        $equipment = Equipment::factory()->create();
        $reservation = Reservation::factory()->create([
            'equipment_id' => $equipment->id,
            'user_id' => $buyer->id,
            'start_date' => today()->addDays(20),
            'end_date' => today()->addDays(22),
            'total_amount' => 100,
            'status' => 'confirmed',
        ]);

        $stripe = $this->mock(StripeConnectService::class);
        $requestedAmounts = [];
        $stripe->shouldReceive('createCheckoutSession')
            ->twice()
            ->withArgs(function (...$arguments) use (&$requestedAmounts): bool {
                $requestedAmounts[] = $arguments[0];

                return true;
            })
            ->andReturn(
                \Stripe\Checkout\Session::constructFrom([
                    'id' => 'cs_test_deposit',
                    'url' => 'https://checkout.stripe.test/deposit',
                    'status' => 'open',
                ]),
                \Stripe\Checkout\Session::constructFrom([
                    'id' => 'cs_test_balance',
                    'url' => 'https://checkout.stripe.test/balance',
                    'status' => 'open',
                ])
            );

        $this->actingAs($buyer)
            ->post(route('booking.reservations.pay', $reservation), ['payment_method' => 'CARD'])
            ->assertRedirect('https://checkout.stripe.test/deposit');

        $deposit = Payment::where('transaction_reference', 'cs_test_deposit')->firstOrFail();
        $this->assertSame(35.7, (float) $deposit->amount);
        $this->assertSame('deposit', $deposit->payment_stage);
        $this->assertSame(today()->toDateString(), $deposit->due_date->toDateString());

        $deposit->update(['status' => 'paid']);
        $paymentPlan = app(ReservationPaymentService::class);
        $this->assertFalse($paymentPlan->synchronizeAfterPayment($reservation));
        $this->assertDatabaseHas('invoices', [
            'reservation_id' => $reservation->id,
            'total' => 119,
            'status' => 'partially_paid',
        ]);
        $this->assertDatabaseMissing('rentals', ['reservation_id' => $reservation->id]);

        $this->actingAs($buyer)
            ->post(route('booking.reservations.pay', $reservation), ['payment_method' => 'CARD'])
            ->assertRedirect('https://checkout.stripe.test/balance');

        $balance = Payment::where('transaction_reference', 'cs_test_balance')->firstOrFail();
        $this->assertSame(83.3, (float) $balance->amount);
        $this->assertSame('balance', $balance->payment_stage);
        $this->assertSame(today()->addDays(13)->toDateString(), $balance->due_date->toDateString());
        $this->assertEquals([11.42, 26.66], $requestedAmounts);

        $balance->update(['status' => 'paid']);
        $this->assertTrue($paymentPlan->synchronizeAfterPayment($reservation));
        $this->assertDatabaseHas('invoices', [
            'reservation_id' => $reservation->id,
            'total' => 119,
            'status' => 'paid',
        ]);
        $this->assertDatabaseHas('rentals', [
            'reservation_id' => $reservation->id,
            'total_amount' => 119,
        ]);
        $this->assertDatabaseCount('rental_contracts', 1);
    }

    public function test_reservations_starting_within_seven_days_require_full_payment(): void
    {
        $buyer = User::factory()->create([
            'role' => UserRole::BUYER,
            'role_setup_completed' => true,
        ]);
        $reservation = Reservation::factory()->create([
            'user_id' => $buyer->id,
            'start_date' => today()->addDays(5),
            'total_amount' => 100,
            'status' => 'confirmed',
        ]);

        $paymentPlan = app(ReservationPaymentService::class);

        $this->assertSame(119.0, $paymentPlan->nextPaymentAmount($reservation));
        $this->assertSame('full', $paymentPlan->nextPaymentStage($reservation));
    }

    public function test_overdue_balance_reminder_is_sent_once(): void
    {
        $buyer = User::factory()->create([
            'role' => UserRole::BUYER,
            'role_setup_completed' => true,
        ]);
        $equipment = Equipment::factory()->create();
        $reservation = Reservation::factory()->create([
            'equipment_id' => $equipment->id,
            'user_id' => $buyer->id,
            'start_date' => today()->addDays(6),
            'total_amount' => 100,
            'status' => 'confirmed',
        ]);
        $reservation->payments()->create([
            'amount' => 35.7,
            'payment_stage' => 'deposit',
            'due_date' => today(),
            'payment_date' => now(),
            'transaction_reference' => 'PAY-OVERDUE-DEPOSIT',
            'status' => 'paid',
            'payment_method' => 'CARD',
        ]);
        Invoice::create([
            'reservation_id' => $reservation->id,
            'invoice_number' => 'INV-OVERDUE-TEST',
            'issue_date' => today(),
            'subtotal' => 100,
            'tax' => 19,
            'total' => 119,
            'status' => 'partially_paid',
        ]);

        Artisan::call('payments:send-overdue-reminders');
        $this->assertSame(
            'Sent 1 balance reminder(s) (3 days before: 0, due today: 0, overdue: 1).',
            trim(Artisan::output())
        );

        $this->assertDatabaseHas('invoices', [
            'reservation_id' => $reservation->id,
        ]);
        $this->assertNotNull($reservation->invoice()->firstOrFail()->balance_reminder_sent_at);
        $this->assertSame(1, Notification::where('notifiable_id', $buyer->id)
            ->where('type', \App\Notifications\OverdueBalanceReminderNotification::class)
            ->count());
        $this->assertStringContainsString(
            '83.30 TND',
            Notification::where('notifiable_id', $buyer->id)->firstOrFail()->data['body']
        );

        Artisan::call('payments:send-overdue-reminders');

        $this->assertSame(1, Notification::where('notifiable_id', $buyer->id)
            ->where('type', \App\Notifications\OverdueBalanceReminderNotification::class)
            ->count());
    }

    public function test_balance_reminders_are_sent_three_days_before_on_due_date_and_after_due_date_once_each(): void
    {
        $buyer = User::factory()->create([
            'role' => UserRole::BUYER,
            'role_setup_completed' => true,
        ]);
        $equipment = Equipment::factory()->create();

        $scenarios = [
            ['stage' => 'three_days', 'days_until_start' => 10, 'title' => 'Balance due in 3 days'],
            ['stage' => 'due_today', 'days_until_start' => 7, 'title' => 'Balance due today'],
            ['stage' => 'overdue', 'days_until_start' => 6, 'title' => 'Overdue balance reminder'],
        ];

        foreach ($scenarios as $scenario) {
            $reservation = Reservation::factory()->create([
                'equipment_id' => $equipment->id,
                'user_id' => $buyer->id,
                'start_date' => today()->addDays($scenario['days_until_start']),
                'total_amount' => 100,
                'status' => 'confirmed',
            ]);
            $reservation->payments()->create([
                'amount' => 35.7,
                'payment_stage' => 'deposit',
                'due_date' => today(),
                'payment_date' => now(),
                'transaction_reference' => 'PAY-REMINDER-'.$scenario['stage'],
                'status' => 'paid',
                'payment_method' => 'CARD',
            ]);
            Invoice::create([
                'reservation_id' => $reservation->id,
                'invoice_number' => 'INV-REMINDER-'.$scenario['stage'],
                'issue_date' => today(),
                'subtotal' => 100,
                'tax' => 19,
                'total' => 119,
                'status' => 'partially_paid',
            ]);
        }

        Artisan::call('payments:send-overdue-reminders');
        $this->assertSame(
            'Sent 3 balance reminder(s) (3 days before: 1, due today: 1, overdue: 1).',
            trim(Artisan::output())
        );

        foreach ($scenarios as $scenario) {
            $notification = Notification::where('notifiable_id', $buyer->id)
                ->where('data->title', $scenario['title'])
                ->firstOrFail();
            $this->assertStringContainsString('83.30 TND', $notification->data['body']);
        }

        Artisan::call('payments:send-overdue-reminders');
        $this->assertSame(
            'Sent 0 balance reminder(s) (3 days before: 0, due today: 0, overdue: 0).',
            trim(Artisan::output())
        );
        $this->assertSame(3, Notification::where('notifiable_id', $buyer->id)->count());
    }
}
