<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class MaintenanceRoutesSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_maintenance_route_handles_anonymous_requests_without_server_error(): void
    {
        $routes = collect(Route::getRoutes()->getRoutes())->filter(fn ($route) =>
            str_starts_with((string) $route->getName(), 'technical.')
            || str_starts_with((string) $route->getName(), 'admin.technical.')
        );

        $this->assertNotEmpty($routes);

        foreach ($routes as $route) {
            $method = collect($route->methods())->first(fn (string $method) => $method !== 'HEAD') ?? 'GET';
            $uri = preg_replace('/\{[^}]+\}/', '1', $route->uri());
            $response = $this->call($method, '/'.$uri, [], [], [], ['HTTP_ACCEPT' => 'application/json']);

            $this->assertLessThan(500, $response->getStatusCode(), $method.' /'.$uri);
        }
    }
}
