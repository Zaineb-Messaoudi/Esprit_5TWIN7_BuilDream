<?php

use App\Services\ReservationAssistantService;
use Tests\TestCase;

uses(TestCase::class);

dataset('realistic reservation assistant requests', function () {
    $examples = json_decode(
        file_get_contents(__DIR__.'/../Fixtures/reservation_assistant_examples.json'),
        true,
        flags: JSON_THROW_ON_ERROR
    );

    return array_map(fn (array $example): array => [$example], $examples);
});

it('extracts the expected structured fields from realistic requests', function (array $example) {
    $result = app(ReservationAssistantService::class)->analyze($example['message']);

    expect($result['status'])->toBe('ready')
        ->and($result['fields']['service_type'])->toBe($example['service_type'])
        ->and($result['fields']['surface_m2'])->toBe((float) $example['surface_m2'])
        ->and($result['fields']['urgency'])->toBe($example['urgency'])
        ->and($result['fields']['budget'])->toBe((float) $example['budget'])
        ->and($result['fields']['address'])->toBe($example['address'])
        ->and($result['fields']['city'])->toBe(trim(substr($example['address'], strrpos($example['address'], ',') + 1)))
        ->and($result['missing_fields'])->toBe([]);
})->with('realistic reservation assistant requests');

it('asks for clarification when the service is unclear instead of guessing', function () {
    $result = app(ReservationAssistantService::class)->analyze('Je cherche de l’aide pour mon projet.');

    expect($result['status'])->toBe('needs_clarification')
        ->and($result['fields']['service_type'])->toBeNull()
        ->and($result['missing_fields'])->toContain('service_type');
});

it('routes work outside the supported catalog to a custom quote', function () {
    $result = app(ReservationAssistantService::class)->analyze('Je veux installer une piscine.');

    expect($result['status'])->toBe('needs_clarification')
        ->and($result['fields']['service_type'])->toBe('custom_quote')
        ->and($result['fields']['custom_description'])->toBe('Je veux installer une piscine.');
});

it('asks for a city when the address does not include one', function () {
    $result = app(ReservationAssistantService::class)->analyze(
        'Plomberie urgente, budget 900 TND, adresse : 12 rue du Lac.'
    );

    expect($result['fields']['city'])->toBeNull()
        ->and($result['missing_fields'])->toContain('city')
        ->and($result['status'])->toBe('needs_clarification');
});
