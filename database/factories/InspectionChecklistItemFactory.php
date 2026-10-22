<?php

namespace Database\Factories;

use App\Models\InspectionChecklist;
use App\Models\InspectionChecklistItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<InspectionChecklistItem> */
class InspectionChecklistItemFactory extends Factory
{
    protected $model = InspectionChecklistItem::class;

    public function definition(): array
    {
        return [
            'inspection_checklist_id' => InspectionChecklist::factory(),
            'label' => $this->faker->sentence(4),
            'is_critical' => $this->faker->boolean(30),
            'sort_order' => $this->faker->numberBetween(1, 20),
        ];
    }
}
