<?php

namespace Database\Factories;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reservation_id' => Reservation::factory(),
            'amount' => \fake()->randomFloat(2, 50, 1000),
            'payment_date' => \fake()->dateTimeBetween('-1 month', 'now'),
            'transaction_reference' => 'TRX-'.strtoupper(\fake()->unique()->bothify('########')),
            'status' => \fake()->randomElement(['pending', 'paid', 'failed']),
            'payment_method' => \fake()->randomElement(['CARD', 'BANK_TRANSFER', 'CASH']),
        ];
    }
}
