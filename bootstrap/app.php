<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        channels: __DIR__.'/../routes/channels.php',
    )
    ->withProviders([
        \App\Providers\EventServiceProvider::class,
        \Laravel\Sanctum\SanctumServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $trustedProxies = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('TRUSTED_PROXIES', ''))
        )));
        $middleware->trustProxies(at: $trustedProxies ?: null);
        $middleware->redirectUsersTo(fn () => route('dashboard'));

        // Security headers middleware
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\RateLimitHeaders::class,
        ]);

        $middleware->alias([
            'role.selected' => \App\Http\Middleware\EnsureRoleSelected::class,
            '2fa' => \App\Http\Middleware\TwoFactorAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            return response()->view('pages.errors.error-404', [
                'title' => __('Page not found'),
            ], 404);
        });
    })
    ->withCommands([
        \App\Console\Commands\ScoutSetupMeilisearch::class,
    ])
    ->create();
