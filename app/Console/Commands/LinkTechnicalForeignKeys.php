<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LinkTechnicalForeignKeys extends Command
{
    protected $signature = 'technical:link-foreign-keys';

    protected $description = 'Add available technical foreign keys after the Equipment module is installed';

    public function handle(): int
    {
        foreach (['equipment', 'maintenances', 'inspections'] as $table) {
            if (! Schema::hasTable($table)) {
                $this->error("Missing table: {$table}. Merge and migrate its module first.");

                return self::FAILURE;
            }
        }

        $rentalsReady = Schema::hasTable('rentals');
        $orphans = [
            'maintenances.equipment_id' => DB::table('maintenances')
                ->leftJoin('equipment', 'maintenances.equipment_id', '=', 'equipment.id')
                ->whereNull('equipment.id')->exists(),
            'inspections.equipment_id' => DB::table('inspections')
                ->leftJoin('equipment', 'inspections.equipment_id', '=', 'equipment.id')
                ->whereNull('equipment.id')->exists(),
        ];
        if ($rentalsReady) {
            $orphans['inspections.rental_id'] = DB::table('inspections')
                ->leftJoin('rentals', 'inspections.rental_id', '=', 'rentals.id')
                ->whereNotNull('inspections.rental_id')->whereNull('rentals.id')->exists();
        }

        foreach ($orphans as $column => $found) {
            if ($found) {
                $this->error("Invalid references in {$column}. Fix these rows before adding foreign keys.");

                return self::FAILURE;
            }
        }

        $this->addForeignKey('maintenances', 'equipment_id', 'equipment', 'restrict');
        $this->addForeignKey('inspections', 'equipment_id', 'equipment', 'restrict');
        if ($rentalsReady) {
            $this->addForeignKey('inspections', 'rental_id', 'rentals', 'set null');
        }

        $this->info($rentalsReady
            ? 'Technical foreign keys are linked.'
            : 'Equipment foreign keys are linked. Run this command again after the Rental module is installed.');

        return self::SUCCESS;
    }

    private function addForeignKey(string $tableName, string $column, string $parent, string $onDelete): void
    {
        $exists = collect(Schema::getForeignKeys($tableName))->contains(
            fn (array $foreignKey) => in_array($column, $foreignKey['columns'] ?? [], true)
        );

        if ($exists) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($column, $parent, $onDelete): void {
            $table->foreign($column)->references('id')->on($parent)->onDelete($onDelete);
        });
    }
}
