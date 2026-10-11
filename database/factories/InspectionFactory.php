<?php

namespace Database\Factories;

use App\Models\Equipment;
use App\Models\Inspection;
use App\Models\Rental;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

/** @extends Factory<Inspection> */
class InspectionFactory extends Factory
{
    protected $model = Inspection::class;

    public function definition(): array
    {
        $equipment = Equipment::query()->inRandomOrder()->firstOrFail();

        return [
            'equipment_id' => $equipment->getKey(),
            'rental_id' => class_exists(Rental::class) && Schema::hasTable('rentals')
                ? Rental::query()->where('equipment_id', $equipment->getKey())->inRandomOrder()->value('id')
                : null,
            'inspection_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'condition_before' => fake()->randomElement(['New', 'Good', 'Fair']),
            'condition_after' => fake()->randomElement(['Good', 'Fair', 'Damaged']),
            'damage_detected' => fake()->boolean(25),
            'comments' => fake()->optional()->sentence(),
        ];
    }
}
