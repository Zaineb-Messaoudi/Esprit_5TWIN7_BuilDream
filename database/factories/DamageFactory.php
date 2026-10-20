<?php

namespace Database\Factories;

use App\Models\Damage;
use App\Models\Inspection;
use App\Models\InspectionChecklistItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Damage> */
class DamageFactory extends Factory
{
    protected $model = Damage::class;

    public function definition(): array
    {
        $inspection = Inspection::factory()->create();

        return [
            'inspection_id' => $inspection->getKey(),
            'equipment_id' => $inspection->equipment_id,
            'inspection_checklist_item_id' => InspectionChecklistItem::factory(),
            'severity' => fake()->randomElement(['minor', 'moderate', 'severe']),
            'repair_cost' => fake()->randomFloat(2, 25, 1500),
            'description' => fake()->sentence(),
            'status' => 'reported',
        ];
    }
}
