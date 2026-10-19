<?php

return [
    'provider' => env('AI_PROVIDER', 'openai'),
    'api_key' => env('AI_API_KEY'),
    'base_url' => env('AI_BASE_URL'),
    'model' => env('AI_MODEL', 'gpt-4o-mini'),
];