<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Keep the technical module usable with a local fixture until the
        // teammate's Equipment model is merged. A real model always wins.
        if (! class_exists(\App\Models\Equipment::class)) {
            class_alias(\App\Support\TechnicalPreviewEquipment::class, \App\Models\Equipment::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
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

        // Force HTTPS in production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
