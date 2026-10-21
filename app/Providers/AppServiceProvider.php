<?php

namespace App\Providers;

use App\Models\Inspection;
use App\Models\Maintenance;
use App\Observers\InspectionObserver;
use App\Observers\MaintenanceObserver;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(\App\Contracts\AiClientInterface::class, \App\Services\Ai\OpenAiCompatibleClient::class);
        $this->app->bind(\App\Contracts\FailureRiskPredictorInterface::class, \App\Services\Maintenance\RuleBasedFailureRiskService::class);
    }

    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Maintenance::class, \App\Policies\TechnicalRecordPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\MaintenanceReport::class, \App\Policies\TechnicalRecordPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Inspection::class, \App\Policies\TechnicalRecordPolicy::class);
        \Illuminate\Support\Facades\Gate::define('technical-access', fn (\App\Models\User $user): bool => $user->isAdmin() || $user->isOwner());

        // Define global role gates
        \Illuminate\Support\Facades\Gate::define('admin-only', function (\App\Models\User $user) {
            return $user->role === \App\Enums\UserRole::ADMIN;
        });

        \Illuminate\Support\Facades\Gate::define('owner-only', function (\App\Models\User $user) {
            return $user->isOwner();
        });
        \Illuminate\Support\Facades\Gate::define('buyer-only', function (\App\Models\User $user) {
            return $user->isBuyer();
        });

        // Register model observers
        Inspection::observe(InspectionObserver::class);
        Maintenance::observe(MaintenanceObserver::class);

        // Register AI prompt versions (only if table exists)
        try {
            if (Schema::hasTable('ai_prompts')) {
                app(\App\Services\PromptVersionService::class)->registerEndpointPrompts();
            }
        } catch (\Throwable $exception) {
            Log::debug('AI prompt registration skipped because the database is unavailable.', [
                'exception' => $exception,
            ]);
        }

        // Force HTTPS in production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
