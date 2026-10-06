<?php

namespace Database\Factories;

use App\Models\Equipment;
use App\Models\Maintenance;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Maintenance> */
class MaintenanceFactory extends Factory
{
    protected $model = Maintenance::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-6 months', '-7 days');

        return [
            // A factory record must always point to an existing equipment row.
            'equipment_id' => Equipment::query()->inRandomOrder()->firstOrFail()->getKey(),
            'start_date' => $start,
            'end_date' => fake()->dateTimeBetween($start, 'now'),
            'reason' => fake()->sentence(),
            'cost' => fake()->randomFloat(2, 0, 750),
            'status' => 'completed',
            'notes' => fake()->optional()->paragraph(),
        ];
    }
}
