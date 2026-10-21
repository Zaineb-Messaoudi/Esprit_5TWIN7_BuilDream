<?php

namespace App\Providers;

use App\Models\Inspection;
use App\Models\Maintenance;
use App\Observers\InspectionObserver;
use App\Observers\MaintenanceObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
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
        if (\Illuminate\Support\Facades\Schema::hasTable('ai_prompts')) {
            app(\App\Services\PromptVersionService::class)->registerEndpointPrompts();
        }

        // Force HTTPS in production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
