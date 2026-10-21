<?php

use App\Services\AiRequestParser;
use Illuminate\Support\Facades\Http;

function geminiReply(array $payload): array
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

it('does nothing when the AI is disabled', function () {
    config()->set('ai.enabled', false);
    Http::fake();

    expect(app(AiRequestParser::class)->parse('plombier a Tunis'))->toBeNull();
    Http::assertNothingSent();
});

it('does nothing when there is no API key', function () {
    config()->set('ai.gemini.key', null);
    Http::fake();

    expect(app(AiRequestParser::class)->parse('plombier a Tunis'))->toBeNull();
    Http::assertNothingSent();
});

it('extracts fields from the model reply', function () {
    Http::fake(['generativelanguage.googleapis.com/*' => Http::response(geminiReply([
        'service_type' => 'plumbing',
        'custom_description' => null,
        'surface_m2' => 12,
        'budget' => 800,
        'urgency' => 'urgent',
        'address' => null,
        'city' => 'Tunis',
    ]))]);

    $fields = app(AiRequestParser::class)->parse('fuite sous evier Tunis 800 dinars urgent');

    expect($fields['service_type'])->toBe('plumbing')
        ->and($fields['surface_m2'])->toBe(12.0)
        ->and($fields['budget'])->toBe(800.0)
        ->and($fields['urgency'])->toBe('urgent')
        ->and($fields['city'])->toBe('Tunis');

    Http::assertSent(fn ($request) => $request->hasHeader('x-goog-api-key', 'test-key')
        && str_contains($request->url(), '/interactions')
        && $request['model'] === 'test-model'
        && data_get($request->data(), 'response_format.0.mime_type') === 'application/json');
});

it('uses Groq JSON mode to extract reservation request fields', function () {
    config()->set('ai.provider', 'groq');
    config()->set('ai.groq.key', 'test-groq-key');
    config()->set('ai.groq.model', 'test-groq-model');
    Http::fake(['api.groq.com/*' => Http::response([
        'choices' => [['message' => ['content' => json_encode([
            'service_type' => 'plumbing', 'custom_description' => null, 'surface_m2' => null,
            'budget' => 700, 'urgency' => 'urgent', 'address' => null, 'city' => 'Tunis',
        ])]]],
    ])]);

    $fields = app(AiRequestParser::class)->parse('Plombier urgent à Tunis, budget 700 TND');

    expect($fields['service_type'])->toBe('plumbing')->and($fields['city'])->toBe('Tunis');
    Http::assertSent(fn ($request) => str_contains($request->url(), '/chat/completions')
        && $request->hasHeader('Authorization', 'Bearer test-groq-key')
        && data_get($request->data(), 'response_format.type') === 'json_object');
});

it('discards invalid values from the model', function () {
    Http::fake(['generativelanguage.googleapis.com/*' => Http::response(geminiReply([
        'service_type' => 'hacking',
        'custom_description' => 'ignored',
        'surface_m2' => 'abc',
        'budget' => -5,
        'urgency' => 'now',
        'address' => '<b>5 rue du Lac</b>',
        'city' => str_repeat('x', 300),
    ]))]);

    $fields = app(AiRequestParser::class)->parse('texte');

    expect($fields['service_type'])->toBeNull()
        ->and($fields['custom_description'])->toBeNull()
        ->and($fields['surface_m2'])->toBeNull()
        ->and($fields['budget'])->toBeNull()
        ->and($fields['urgency'])->toBeNull()
        ->and($fields['address'])->toBe('5 rue du Lac')
        ->and(mb_strlen($fields['city']))->toBe(100);
});

it('returns null when the service fails', function () {
    Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => 'down'], 500)]);

    expect(app(AiRequestParser::class)->parse('plombier a Tunis'))->toBeNull();
});

it('returns null when the reply is not valid JSON', function () {
    Http::fake(['generativelanguage.googleapis.com/*' => Http::response(
        ['output_text' => 'not json']
    )]);

    expect(app(AiRequestParser::class)->parse('plombier a Tunis'))->toBeNull();
});

it('keeps rule values and lets the AI fill only the gaps', function () {
    $result = app(AiRequestParser::class)->merge(
        ['service_type' => 'plumbing', 'city' => null, 'budget' => 800.0, 'surface_m2' => null],
        ['service_type' => 'masonry', 'city' => 'Sfax', 'budget' => 5000.0, 'surface_m2' => 20.0],
    );

    expect($result['fields']['service_type'])->toBe('plumbing')
        ->and($result['fields']['budget'])->toBe(800.0)
        ->and($result['fields']['city'])->toBe('Sfax')
        ->and($result['fields']['surface_m2'])->toBe(20.0)
        ->and($result['ai_filled'])->toEqualCanonicalizing(['city', 'surface_m2']);
});

it('ignores the AI custom description when the service is not a custom quote', function () {
    $result = app(AiRequestParser::class)->merge(
        ['service_type' => 'plumbing', 'custom_description' => null],
        ['custom_description' => 'Construire une piscine'],
    );

    expect($result['fields']['custom_description'])->toBeNull()
        ->and($result['ai_filled'])->toBe([]);
});
