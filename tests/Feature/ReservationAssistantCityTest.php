<?php

use App\Services\ReservationAssistantService;

it('detects a known city anywhere in the sentence', function (string $text, ?string $city) {
    $result = (new ReservationAssistantService)->analyze($text);

    expect($result['fields']['city'])->toBe($city);
})->with([
    ['Je cherche un plombier urgent à Tunis, budget 800 TND', 'Tunis'],
    ['Repeindre mon salon de 30 m2 à Sfax', 'Sfax'],
    ['Besoin d\'un électricien pour changer le tableau, Sousse', 'Sousse'],
    ['Un plombier à Gabès pour une fuite', 'Gabès'],
    ['Travaux de peinture à Ben Arous', 'Ben Arous'],
    ['Rénover ma cuisine, budget 5000 TND', null],
    ['Je cherche un plombier, adresse : 12 rue du Lac, Tunis', 'Tunis'],
]);
