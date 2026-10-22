<?php

namespace Database\Factories;

use App\Models\InspectionChecklist;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<InspectionChecklist> */
class InspectionChecklistFactory extends Factory
{
    protected $model = InspectionChecklist::class;

    public function definition(): array
    {
        return [
            'category_id' => $this->faker->numberBetween(1, 20),
            'category_name' => $this->faker->words(2, true),
            'name' => $this->faker->words(3, true),
            'is_active' => true,
        ];
    }
}
