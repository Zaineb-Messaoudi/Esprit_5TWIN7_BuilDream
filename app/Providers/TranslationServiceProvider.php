<?php

namespace App\Providers;

use App\Console\Commands\SyncBladeTranslations;
use App\Services\AutomatedTranslationService;
use Illuminate\Support\ServiceProvider;

class TranslationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AutomatedTranslationService::class, function ($app) {
            return new AutomatedTranslationService;
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncBladeTranslations::class,
            ]);
        }
    }
}
