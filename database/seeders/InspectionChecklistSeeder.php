<?php

namespace Database\Seeders;

use App\Models\InspectionChecklist;
use App\Support\FrontDemo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InspectionChecklistSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Schema::hasTable('categories')
            ? DB::table('categories')->orderBy('id')->get(['id', 'name'])
            : FrontDemo::categories()->map(fn ($category) => (object) ['id' => $category->id, 'name' => $category->name]);

        foreach ($categories as $category) {
            $checklist = InspectionChecklist::query()->updateOrCreate(
                ['category_id' => $category->id],
                ['category_name' => $category->name, 'name' => $category->name.' safety inspection', 'is_active' => true],
            );

            foreach ($this->itemsFor((string) $category->name) as $position => [$label, $critical]) {
                $checklist->items()->updateOrCreate(
                    ['label' => $label],
                    ['is_critical' => $critical, 'sort_order' => $position + 1],
                );
            }
        }
    }

    /** @return list<array{string, bool}> */
    private function itemsFor(string $category): array
    {
        $category = mb_strtolower($category);

        return match (true) {
            str_contains($category, 'panel') => [
                ['No cracked photovoltaic cells', true],
                ['Cable insulation intact', true],
                ['Connectors lock correctly', true],
                ['Frame and hinges undamaged', false],
                ['Surface is clean', false],
            ],
            str_contains($category, 'batter') => [
                ['Battery casing is not swollen', true],
                ['No overheating or burning smell', true],
                ['Charging port is intact', true],
                ['Display and indicators work', false],
                ['Accessories are complete', false],
            ],
            default => [
                ['Rotor and blades have no cracks', true],
                ['Brake and emergency stop work', true],
                ['Electrical cable is intact', true],
                ['Fasteners are secure', false],
                ['Protective case is complete', false],
            ],
        };
    }
}
