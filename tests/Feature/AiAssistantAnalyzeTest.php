<?php

use App\Services\AiRequestParser;
use Illuminate\Support\Facades\Http;

function aiAssistantReply(array $payload): array
{
    return ['output_text' => json_encode($payload)];
}

beforeEach(function () {
    config()->set('ai.enabled', true);
    config()->set('ai.provider', 'gemini');
    config()->set('ai.gemini.key', 'test-key');
    config()->set('ai.gemini.model', 'test-model');
    Http::preventStrayRequests();
});

it('lets the AI fill what the rules could not find and says so', function () {
    Http::fake(['generativelanguage.googleapis.com/*' => Http::response(aiAssistantReply([
        'service_type' => 'waterproofing',
        'custom_description' => null,
        'surface_m2' => null,
        'budget' => 600,
        'urgency' => null,
        'address' => null,
        'city' => 'Sousse',
    ]))]);

    $result = app(AiRequestParser::class)->analyze('Il y a de l eau qui goutte du plafond de ma chambre a Sousse, je peux mettre 600 dinars');

    expect($result['fields']['service_type'])->toBe('waterproofing')
        ->and($result['fields']['city'])->toBe('Sousse')
        ->and($result['fields']['budget'])->toBe(600.0)
        ->and($result['ai_used'])->toBeTrue()
        ->and($result['ai_filled'])->toBe(['service_type'])
        ->and($result['status'])->toBe('needs_clarification')
        ->and($result['missing_fields'])->not->toContain('service_type')
        ->and($result['message'])->toContain(__('Service type'));
});

it('does not call the AI when the rules found everything', function () {
    Http::fake();

    $result = app(AiRequestParser::class)->analyze('Je cherche un plombier urgent, budget 800 TND, adresse : 12 rue du Lac, Tunis');

    expect($result['status'])->toBe('ready')
        ->and($result['ai_used'])->toBeFalse()
        ->and($result['ai_filled'])->toBe([]);

    Http::assertNothingSent();
});

it('falls back to the rules when the AI service fails', function () {
    Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => 'down'], 500)]);

    $result = app(AiRequestParser::class)->analyze('Il y a de l eau qui goutte du plafond de ma chambre a Sousse, je peux mettre 600 dinars');

    expect($result['ai_used'])->toBeFalse()
        ->and($result['fields']['service_type'])->toBeNull()
        ->and($result['fields']['city'])->toBe('Sousse')
        ->and($result['status'])->toBe('needs_clarification');
});

it('never calls the AI when it is disabled', function () {
    config()->set('ai.enabled', false);
    Http::fake();

    $result = app(AiRequestParser::class)->analyze('Il y a de l eau qui goutte du plafond de ma chambre a Sousse');

    expect($result['ai_used'])->toBeFalse();

    Http::assertNothingSent();
});
