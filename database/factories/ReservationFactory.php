<?php

namespace Database\Factories;

use App\Models\Equipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('now', '+2 months');
        $end = (clone $start)->modify('+'.rand(1, 10).' days');

        return [
            'reference' => 'RES-'.strtoupper($this->faker->unique()->bothify('????-####')),
            'equipment_id' => Equipment::factory(),
            'user_id' => User::factory(),
            'start_date' => $start,
            'end_date' => $end,
            'total_amount' => $this->faker->randomFloat(2, 100, 2000),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'cancelled']),
        ];
    }
}
