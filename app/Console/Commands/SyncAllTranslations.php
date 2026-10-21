<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncAllTranslations extends Command
{
    protected $signature = 'translations:sync {--dry-run : Show what would be translated without modifying}';

    protected $description = 'Sync all translations (Blade views + database content)';

    public function handle(): int
    {
        $this->call('translations:sync-views', ['--dry-run' => $this->option('dry-run')]);
        $this->call('translations:sync-db', ['--dry-run' => $this->option('dry-run')]);

        $this->info('All translation sync complete.');

        return self::SUCCESS;
    }
}
