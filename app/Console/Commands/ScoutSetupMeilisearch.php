<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Scout\Facades\Scout;
use Meilisearch\Client;

class ScoutSetupMeilisearch extends Command
{
    protected $signature = 'scout:setup-meilisearch
                            {--reindex : Reindex all searchable models}
                            {--settings-only : Only update index settings without reindexing}';

    protected $description = 'Configure Meilisearch indexes with optimal settings for SolarShare';

    public function handle(): int
    {
        $driver = config('scout.driver');

        if ($driver !== 'meilisearch') {
            $this->error("SCOUT_DRIVER is not set to 'meilisearch'. Current: {$driver}");
            $this->info('Set SCOUT_DRIVER=meilisearch in your .env file');

            return self::FAILURE;
        }

        $host = config('scout.meilisearch.host');
        $key = config('scout.meilisearch.key');

        if (! $host) {
            $this->error('MEILISEARCH_HOST is not configured');

            return self::FAILURE;
        }

        $this->info("Connecting to Meilisearch at {$host}...");

        try {
            $client = new Client($host, $key);
            $health = $client->health();
            $this->info('Meilisearch is healthy: '.($health['status'] === 'available' ? 'YES' : 'NO'));
        } catch (\Exception $e) {
            $this->error("Cannot connect to Meilisearch: {$e->getMessage()}");

            return self::FAILURE;
        }

        // Configure Equipment index
        $this->configureEquipmentIndex($client);

        if ($this->option('reindex')) {
            $this->reindexModels();
        }

        $this->info('Meilisearch setup completed successfully!');

        return self::SUCCESS;
    }

    protected function configureEquipmentIndex(Client $client): void
    {
        $indexName = 'equipment';
        $settings = \App\Models\Equipment::getIndexSettings();

        $this->info("Configuring index: {$indexName}");

        try {
            $index = $client->index($indexName);

            // Update settings
            $task = $index->updateSettings($settings);
            $client->waitForTask($task['taskUid']);
            $this->info("Index settings updated for {$indexName}");
        } catch (\Exception $e) {
            $this->warn("Could not update settings for {$indexName}: {$e->getMessage()}");
        }
    }

    protected function reindexModels(): void
    {
        $models = [
            \App\Models\Equipment::class,
        ];

        foreach ($models as $model) {
            $this->info("Reindexing {$model}...");
            try {
                $model::query()->chunkById(500, function ($chunk) {
                    Scout::searchable($chunk);
                });
                $this->info("Reindexed {$model} successfully");
            } catch (\Exception $e) {
                $this->error("Failed to reindex {$model}: {$e->getMessage()}");
            }
        }
    }
}
