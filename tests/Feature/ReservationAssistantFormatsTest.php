<?php

use App\Services\ReservationAssistantService;

it('reads the budget in several formats', function (string $text, ?float $budget) {
    $result = (new ReservationAssistantService)->analyze($text);

    expect($result['fields']['budget'])->toBe($budget);
})->with([
    ['Je cherche un plombier, budget 800 TND', 800.0],
    ['Peinture du salon, 2000 dinars', 2000.0],
    ['Renovation de la cuisine, budget 2k', 2000.0],
    ['Besoin d\'un electricien, budget de 1 500 TND', 1500.0],
    ['Plombier pour 350 DT', 350.0],
    ['Peinture de 30 m2, budget 1200 TND', 1200.0],
    ['Peindre un mur de 30 m2', null],
    ['Je veux un plombier', null],
]);

it('reads the surface in several formats', function (string $text, ?float $surface) {
    $result = (new ReservationAssistantService)->analyze($text);

    expect($result['fields']['surface_m2'])->toBe($surface);
})->with([
    ['Peindre un salon de 30 m2', 30.0],
    ['Peindre un salon de 30 m²', 30.0],
    ['Cuisine de 12 metres carres', 12.0],
    ['Chambre de 15m2', 15.0],
    ['Salle de 12,5 m2', 12.5],
    ['Peindre un mur', null],
]);
