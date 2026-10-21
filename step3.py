p = 'app/Services/ReservationAssistantService.php'
s = open(p, encoding='utf-8', newline='').read()

if "'air_conditioning'" in s:
    print('Deja fait : rien a modifier')
    raise SystemExit

# Services specifiques, places AVANT les services generaux.
new_services = """            'solar' => [
                'label' => __('Solar panels'),
                'terms' => ['chauffe eau solaire', 'panneaux solaires', 'panneau solaire', 'photovoltaique', 'solaire'],
            ],
            'waterproofing' => [
                'label' => __('Waterproofing'),
                'terms' => ['etancheite', 'infiltrations', 'infiltration', 'humidite', 'toiture qui fuit', 'terrasse qui fuit'],
            ],
            'air_conditioning' => [
                'label' => __('Air conditioning'),
                'terms' => ['climatisation', 'climatiseur', 'clim', 'split', 'chauffage'],
            ],
            'plastering' => [
                'label' => __('Plastering'),
                'terms' => ['faux plafond', 'platrier', 'platre', 'placo', 'staff'],
            ],
            'masonry' => [
                'label' => __('Masonry'),
                'terms' => ['maconnerie', 'macon', 'cloison', 'beton', 'dalle', 'parpaing'],
            ],
            'gardening' => [
                'label' => __('Gardening'),
                'terms' => ['jardinage', 'jardinier', 'jardin', 'pelouse', 'taille de haies'],
            ],
"""

anchor = "            'renovation' => ["
assert anchor in s, 'ancre renovation introuvable'
s = s.replace(anchor, new_services + anchor, 1)

# Synonymes supplementaires pour les services existants.
def add_terms(s, old, new):
    assert old in s, 'terme introuvable : ' + old
    return s.replace(old, new, 1)

s = add_terms(s, "'canalisation', 'robinet', 'fuite']",
                 "'canalisation', 'robinet', 'fuite', 'chauffe eau', 'siphon', 'wc', 'sanitaire', 'evier', 'douche']")
s = add_terms(s, "'prises', 'prise', 'cablage']",
                 "'prises', 'prise', 'cablage', 'interrupteur', 'disjoncteur', 'luminaire', 'eclairage']")
s = add_terms(s, "'peintre']", "'peintre', 'vernis', 'enduit', 'ravalement']")
s = add_terms(s, "'carreleur', 'carreler']", "'carreleur', 'carreler', 'faience', 'marbre', 'sol']")
s = add_terms(s, "'fenetre', 'porte']", "'fenetre', 'porte', 'placard', 'serrure', 'parquet', 'volet', 'bois']")

open(p, 'w', encoding='utf-8', newline='').write(s)
print('Service modifie')

test = r'''<?php

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
'''
open('tests/Feature/ReservationAssistantServicesTest.php', 'w', encoding='utf-8', newline='\n').write(test)
print('Test cree')