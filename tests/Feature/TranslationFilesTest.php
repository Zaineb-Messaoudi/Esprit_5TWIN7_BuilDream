<?php

it('has valid json translation files', function () {
    foreach (['en', 'fr'] as $locale) {
        $data = json_decode(file_get_contents(lang_path("$locale.json")), true);

        expect(json_last_error())->toBe(JSON_ERROR_NONE, "$locale.json is invalid");
        expect($data)->toBeArray();
    }
});

it('has french translations for the artisan feature', function () {
    $fr = json_decode(file_get_contents(lang_path('fr.json')), true);

    foreach ([
        'Artisan recommendations',
        'Find a suitable artisan',
        'Save artisan profile',
        'Looking for suitable artisans…',
    ] as $key) {
        expect($fr)->toHaveKey($key);
    }
});
