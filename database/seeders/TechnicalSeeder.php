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
        // Seed every local preview item so every owner account can test its own pages.
        $equipment = Schema::hasColumn('equipment', 'technical_preview')
            ? $query->where('technical_preview', true)->get()
            : $query->limit(5)->get();
        if ($equipment->isEmpty()) {
            throw new RuntimeException('TechnicalSeeder needs at least one real equipment row.');
        }

        $equipment->each(function (Equipment $item) use ($rentalsReady): void {
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
        });
    }
}
