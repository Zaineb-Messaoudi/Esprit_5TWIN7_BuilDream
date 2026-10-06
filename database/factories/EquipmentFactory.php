<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Equipment> */
class EquipmentFactory extends Factory
{
    protected $model = Equipment::class;

    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'owner_id' => User::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'brand' => fake()->company(),
            'model' => strtoupper(fake()->bothify('??-###')),
            'price_per_day' => fake()->randomFloat(2, 5, 150),
            'condition' => fake()->randomElement(['new', 'good', 'fair']),
            'location' => fake()->city(),
            'status' => 'available',
        ];
    }
}