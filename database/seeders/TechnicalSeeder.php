<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\Inspection;
use App\Models\Maintenance;
use App\Models\MaintenanceReport;
use App\Models\Rental;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class TechnicalSeeder extends Seeder
{
    public function run(): void
    {
        if (! class_exists(Equipment::class) || ! Schema::hasTable('equipment')
            || ! Schema::hasColumn('equipment', 'owner_id')
            || ! Schema::hasTable('maintenances') || ! Schema::hasTable('maintenance_reports')
            || ! Schema::hasTable('inspections')) {
            throw new RuntimeException('TechnicalSeeder needs the Equipment model and an equipment table with owner_id. Merge and migrate the complete Equipment module first.');
        }

        $rentalsReady = class_exists(Rental::class) && Schema::hasTable('rentals');

        $query = Equipment::query()->orderBy('id');
        // Seed for all real equipment (skip technical_preview items if they exist)
        $equipment = Schema::hasColumn('equipment', 'technical_preview')
            ? $query->where('technical_preview', false)->get()
            : $query->get();
        if ($equipment->isEmpty()) {
            throw new RuntimeException('TechnicalSeeder needs at least one real equipment row.');
        }

        // Seed without model events: factory-generated statuses must not flip the
        // catalogue's equipment statuses through the observers.
        \Illuminate\Database\Eloquent\Model::withoutEvents(fn () => $equipment->each(function (Equipment $item) use ($rentalsReady): void {
            $maintenance = Maintenance::query()->firstOrCreate(
                ['equipment_id' => $item->getKey(), 'reason' => 'Routine electrical safety check'],
                Maintenance::factory()->make([
                    'equipment_id' => $item->getKey(),
                    'reason' => 'Routine electrical safety check',
                ])->getAttributes(),
            );

            MaintenanceReport::query()->firstOrCreate(
                ['maintenance_id' => $maintenance->getKey()],
                MaintenanceReport::factory()->make([
                    'maintenance_id' => $maintenance->getKey(),
                ])->getAttributes(),
            );

            $rentalId = $rentalsReady
                ? Rental::query()->where('equipment_id', $item->getKey())->orderBy('id')->value('id')
                : null;
            Inspection::query()->firstOrCreate(
                ['equipment_id' => $item->getKey(), 'comments' => 'Routine return inspection'],
                Inspection::factory()->make([
                    'equipment_id' => $item->getKey(),
                    'rental_id' => $rentalId,
                    'comments' => 'Routine return inspection',
                ])->getAttributes(),
            );
        }));
    }
}
