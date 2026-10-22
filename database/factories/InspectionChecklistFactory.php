<?php

namespace Database\Factories;

use App\Models\InspectionChecklist;
use Illuminate\Database\Eloquent\Factories\Factory;
use function fake;

/** @extends Factory<InspectionChecklist> */
class InspectionChecklistFactory extends Factory
{
    protected $model = InspectionChecklist::class;

    public function definition(): array
    {
        return [
            'category_id' => fake()->numberBetween(1, 20),
            'category_name' => fake()->words(2, true),
            'name' => fake()->words(3, true),
            'is_active' => true,
        ];
    }
}
