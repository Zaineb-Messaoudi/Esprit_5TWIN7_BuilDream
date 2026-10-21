<?php

return [
    'default_source_locale' => env('TRANSLATION_SOURCE_LOCALE', 'en'),
    'target_locales' => [
        'fr',
    ],
    'driver' => env('TRANSLATION_DRIVER', 'libretranslate'),
    'libretranslate' => [
        'endpoint' => env('LIBRETRANSLATE_ENDPOINT', 'https://libretranslate.de'),
        'api_key' => env('LIBRETRANSLATE_API_KEY'),
        'timeout' => env('LIBRETRANSLATE_TIMEOUT', 30),
        'rate_limit_ms' => env('LIBRETRANSLATE_RATE_LIMIT_MS', 200),
    ],
    'cache_ttl_minutes' => env('TRANSLATION_CACHE_TTL_MINUTES', 10080),
    'auto_translatable_models' => [
        \App\Models\Equipment::class => ['name', 'description'],
    ],
];
