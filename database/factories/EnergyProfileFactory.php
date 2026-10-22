<?php

namespace Database\Factories;

use App\Models\EnergyProfile;
use App\Models\Equipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<EnergyProfile> */
class EnergyProfileFactory extends Factory
{
    protected $model = EnergyProfile::class;

    public function definition(): array
    {
        return [
            'equipment_id' => Equipment::factory(),
            'power_watts' => $this->faker->numberBetween(100, 2500),
            'voltage' => $this->faker->randomFloat(2, 12, 48),
            'capacity_wh' => $this->faker->numberBetween(500, 5000),
            'efficiency' => $this->faker->randomFloat(2, 70, 99),
            'technology' => $this->faker->randomElement(['Lithium-ion', 'Monocrystalline', 'Horizontal axis']),
            'max_output' => $this->faker->numberBetween(100, 3000),
            'operating_duration' => $this->faker->randomFloat(2, 1, 24),
        ];
    }
}
