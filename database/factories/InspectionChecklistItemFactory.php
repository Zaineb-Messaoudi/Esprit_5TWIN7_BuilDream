<?php

namespace Database\Factories;

use App\Models\InspectionChecklist;
use App\Models\InspectionChecklistItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use function fake;

/** @extends Factory<InspectionChecklistItem> */
class InspectionChecklistItemFactory extends Factory
{
    protected $model = InspectionChecklistItem::class;

    public function definition(): array
    {
        return [
            'inspection_checklist_id' => InspectionChecklist::factory(),
            'label' => fake()->sentence(4),
            'is_critical' => fake()->boolean(30),
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }
}
