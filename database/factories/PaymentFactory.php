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
            'amount' => $this->faker->randomFloat(2, 50, 1000),
            'payment_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'transaction_reference' => 'TRX-'.strtoupper($this->faker->unique()->bothify('########')),
            'status' => $this->faker->randomElement(['pending', 'paid', 'failed']),
            'payment_method' => $this->faker->randomElement(['CARD', 'BANK_TRANSFER', 'CASH']),
        ];
    }
}
