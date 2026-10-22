<?php

namespace Database\Factories;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 2000);
        $tax = round($subtotal * 0.19, 2);

        return [
            'reservation_id' => Reservation::factory(),
            'invoice_number' => 'INV-'.fake()->unique()->numerify('######'),
            'issue_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
            'status' => fake()->randomElement(['unpaid', 'paid']),
        ];
    }
}
