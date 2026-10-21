<?php

use App\Services\ReservationAssistantService;

it('recognizes the service from natural phrases', function (string $text, string $service) {
    $result = (new ReservationAssistantService)->analyze($text);

    expect($result['fields']['service_type'])->toBe($service);
})->with([
    ['Je veux installer une climatisation dans le salon', 'air_conditioning'],
    ['Mon climatiseur ne marche plus', 'air_conditioning'],
    ['Besoin d\'un macon pour construire une cloison', 'masonry'],
    ['Installer des panneaux solaires sur le toit', 'solar'],
    ['Installation d\'un chauffe-eau solaire', 'solar'],
    ['Probleme d\'etancheite sur la terrasse', 'waterproofing'],
    ['Ma toiture qui fuit depuis la pluie', 'waterproofing'],
    ['Pose d\'un faux plafond', 'plastering'],
    ['Je cherche un jardinier pour la pelouse', 'gardening'],
    ['Mon robinet fuit', 'plumbing'],
    ['Changer le siphon de l\'evier', 'plumbing'],
    ['Remplacer le disjoncteur', 'electricity'],
    ['Poser de la faience dans la salle de bain', 'tiling'],
    ['Changer la serrure de la porte', 'carpentry'],
    ['Peindre le salon', 'painting'],
]);
