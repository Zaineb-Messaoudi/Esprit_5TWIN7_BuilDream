<?php

use App\Services\AiService;

it('reports an AI health failure as an unhealthy response without exposing exception details', function () {
    $this->mock(AiService::class, function ($mock): void {
        $mock->shouldReceive('isConfigured')
            ->andThrow(new RuntimeException('internal provider credentials leaked'));
    });

    $this->getJson(route('health'))
        ->assertStatus(503)
        ->assertJsonPath('status', 'unhealthy')
        ->assertJsonPath('checks.ai.status', 'failed')
        ->assertJsonPath('checks.ai.error', 'AI health check unavailable')
        ->assertJsonMissing(['error' => 'internal provider credentials leaked']);
});
