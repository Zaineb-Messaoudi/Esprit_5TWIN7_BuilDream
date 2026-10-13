<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanupTechnicalPreview extends Command
{
    protected $signature = 'technical:preview-cleanup {--force : Delete local preview data and its equipment fixture table}';

    protected $description = 'Remove only the local technical equipment fixture before merging the real Equipment module';

    public function handle(): int
    {
        if (! Schema::hasTable('equipment') || ! Schema::hasColumn('equipment', 'technical_preview')) {
            $this->error('No technical preview equipment table was found. Nothing was removed.');

            return self::FAILURE;
        }

        if (DB::table('equipment')->where('technical_preview', false)->exists()) {
            $this->error('The equipment table contains non-preview rows. Nothing was removed.');

            return self::FAILURE;
        }

        $equipmentIds = DB::table('equipment')->pluck('id')->all();
        $maintenanceIds = DB::table('maintenances')->whereIn('equipment_id', $equipmentIds)->pluck('id')->all();
        $this->warn('This removes the preview equipment table and all technical records linked to its '.count($equipmentIds).' equipment rows.');

        if (! $this->option('force')) {
            $this->info('Run php artisan technical:preview-cleanup --force when you are ready to remove this local fixture.');

            return self::FAILURE;
        }

        DB::table('maintenance_reports')->whereIn('maintenance_id', $maintenanceIds)->delete();
        DB::table('maintenances')->whereIn('equipment_id', $equipmentIds)->delete();
        DB::table('inspections')->whereIn('equipment_id', $equipmentIds)->delete();

        foreach (['maintenances', 'inspections'] as $tableName) {
            foreach (Schema::getForeignKeys($tableName) as $foreignKey) {
                if (in_array('equipment_id', $foreignKey['columns'] ?? [], true)) {
                    Schema::table($tableName, fn (Blueprint $table) => $table->dropForeign($foreignKey['name']));
                }
            }
        }

        Schema::drop('equipment');
        $this->info('Technical preview equipment and related technical records removed.');

        return self::SUCCESS;
    }
}
