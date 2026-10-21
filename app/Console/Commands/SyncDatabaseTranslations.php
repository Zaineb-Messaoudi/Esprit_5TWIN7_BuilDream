<?php

namespace App\Console\Commands;

use App\Services\AutomatedTranslationService;
use Illuminate\Console\Command;

class SyncDatabaseTranslations extends Command
{
    protected $signature = 'translations:sync-db {--dry-run : Show what would be translated without modifying}';

    protected $description = 'Scan translatable model fields and auto-translate to target locales';

    protected AutomatedTranslationService $translator;

    public function __construct(AutomatedTranslationService $translator)
    {
        parent::__construct();
        $this->translator = $translator;
    }

    public function handle(): int
    {
        $translatableModels = config('translation.auto_translatable_models', []);

        if (empty($translatableModels)) {
            $this->warn('No translatable models configured in config/translation.php');

            return self::SUCCESS;
        }

        foreach ($translatableModels as $modelClass => $fields) {
            if (! class_exists($modelClass)) {
                $this->warn("Model {$modelClass} does not exist, skipping.");

                continue;
            }

            $model = new $modelClass;
            $tableName = $model->getTable();
            $recordCount = $modelClass::count();

            $this->info("Found {$recordCount} record(s) in {$tableName} with translatable fields: ".implode(', ', $fields));

            if ($this->option('dry-run')) {
                $this->warn('[DRY RUN] Would translate each record.');

                continue;
            }

            $bar = $this->output->createProgressBar($recordCount);
            $bar->start();

            $modelClass::each(function ($record) {
                $record->syncTranslations();
                $bar->advance();
            });

            $bar->finish();
            $this->newLine();
        }

        $this->info('Database translation sync complete.');

        return self::SUCCESS;
    }
}
