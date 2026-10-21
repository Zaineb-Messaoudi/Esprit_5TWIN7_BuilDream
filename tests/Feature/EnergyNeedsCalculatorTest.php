<?php

use App\Services\EnergyNeedsCalculator;

it('sizes a small lighting system', function () {
    $result = (new EnergyNeedsCalculator)->calculate([['key' => 'led_lamp', 'quantity' => 2]]);

    expect($result['status'])->toBe('ok')
        ->and($result['daily_wh'])->toEqual(100)
        ->and($result['battery_wh'])->toEqual(200)
        ->and($result['panel_w'])->toEqual(50)
        ->and($result['inverter_w'])->toEqual(100);
});

it('accounts for the starting surge of a refrigerator', function () {
    $result = (new EnergyNeedsCalculator)->calculate([['key' => 'fridge', 'quantity' => 1]]);

    expect($result['daily_wh'])->toEqual(1200)
        ->and($result['battery_wh'])->toEqual(1700)
        ->and($result['panel_w'])->toEqual(400)
        ->and($result['inverter_w'])->toEqual(600);
});

it('increases the battery with more days of autonomy', function () {
    $result = (new EnergyNeedsCalculator)->calculate([['key' => 'led_lamp', 'quantity' => 2]], 2.0);

    expect($result['battery_wh'])->toEqual(300);
});

it('uses custom hours when provided', function () {
    $result = (new EnergyNeedsCalculator)->calculate([['key' => 'led_lamp', 'quantity' => 2, 'hours' => 10]]);

    expect($result['daily_wh'])->toEqual(200)
        ->and($result['battery_wh'])->toEqual(300)
        ->and($result['panel_w'])->toEqual(100);
});

it('returns an empty result when no known appliance is given', function () {
    $result = (new EnergyNeedsCalculator)->calculate([['key' => 'unknown_device', 'quantity' => 3]]);

    expect($result['status'])->toBe('empty');
});

it('ignores unknown appliances but keeps the valid ones', function () {
    $result = (new EnergyNeedsCalculator)->calculate([
        ['key' => 'unknown_device', 'quantity' => 3],
        ['key' => 'led_lamp', 'quantity' => 2],
    ]);

    expect($result['status'])->toBe('ok')
        ->and($result['daily_wh'])->toEqual(100)
        ->and($result['lines'])->toHaveCount(1);
});
