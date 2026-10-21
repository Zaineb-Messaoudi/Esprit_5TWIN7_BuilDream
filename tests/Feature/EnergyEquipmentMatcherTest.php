<?php

use App\Models\EnergyProfile;
use App\Models\Equipment;
use App\Services\EnergyEquipmentMatcher;
use App\Services\EnergyNeedsCalculator;

function makeEnergyListing(int $id, string $name, float $price, array $profile): Equipment
{
    $listing = (new Equipment)->forceFill([
        'id' => $id,
        'name' => $name,
        'price_per_day' => $price,
        'location' => 'Tunis',
    ]);
    $listing->setRelation('energyProfile', (new EnergyProfile)->forceFill($profile));

    return $listing;
}

function fridgeNeeds(): array
{
    return (new EnergyNeedsCalculator)->calculate([['key' => 'fridge', 'quantity' => 1]]);
}

it('ranks listings by the daily cost needed to cover the need', function () {
    $matches = (new EnergyEquipmentMatcher)->match(fridgeNeeds(), [
        makeEnergyListing(1, 'Panel 300 W', 10, ['power_watts' => 300]),
        makeEnergyListing(2, 'Panel 400 W', 25, ['power_watts' => 400]),
        makeEnergyListing(3, 'Battery 2000 Wh', 15, ['capacity_wh' => 2000]),
        makeEnergyListing(4, 'Inverter 500 W', 5, ['max_output' => 500]),
    ]);

    expect($matches['generation'][0]['equipment_id'])->toBe(1)
        ->and($matches['generation'][0]['units'])->toBe(2)
        ->and($matches['generation'][0]['daily_cost'])->toEqual(20)
        ->and($matches['storage'][0]['equipment_id'])->toBe(3)
        ->and($matches['inverter'][0]['equipment_id'])->toBe(4)
        ->and($matches['inverter'][0]['units'])->toBe(2);
});

it('ignores listings without an energy profile or needing too many units', function () {
    $noProfile = (new Equipment)->forceFill(['id' => 9, 'name' => 'No profile', 'price_per_day' => 5, 'location' => 'Tunis']);
    $noProfile->setRelation('energyProfile', null);

    $matches = (new EnergyEquipmentMatcher)->match(fridgeNeeds(), [
        $noProfile,
        makeEnergyListing(10, 'Tiny panel', 1, ['power_watts' => 10]),
    ]);

    expect($matches['generation'])->toBe([])
        ->and($matches['storage'])->toBe([])
        ->and($matches['inverter'])->toBe([]);
});

it('returns no suggestions when there is no listing', function () {
    $matches = (new EnergyEquipmentMatcher)->match(fridgeNeeds(), []);

    expect($matches)->toBe(['generation' => [], 'storage' => [], 'inverter' => []]);
});

it('has french translations for the energy calculator', function () {
    $fr = json_decode(file_get_contents(lang_path('fr.json')), true);

    foreach ([
        'Energy needs calculator', 'Calculate my needs', 'Your estimated needs',
        'Refrigerator', 'Air conditioning', 'Masonry',
    ] as $key) {
        expect($fr)->toHaveKey($key);
    }
});
