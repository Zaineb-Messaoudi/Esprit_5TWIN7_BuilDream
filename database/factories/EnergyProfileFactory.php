<?php

namespace Database\Factories;

use App\Models\EnergyProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<EnergyProfile> */
class EnergyProfileFactory extends Factory
{
    protected $model = EnergyProfile::class;

    public function definition(): array
    {
        return [
            'power_watts' => fake()->numberBetween(100, 2500),
            'voltage' => fake()->randomFloat(2, 12, 48),
            'capacity_wh' => fake()->numberBetween(500, 5000),
            'efficiency' => fake()->randomFloat(2, 70, 99),
            'technology' => fake()->randomElement(['Lithium-ion', 'Monocrystalline', 'Horizontal axis']),
            'max_output' => fake()->numberBetween(100, 3000),
            'operating_duration' => fake()->randomFloat(2, 1, 24),
        ];
    }
}
