<?php

return [
    // Off by default: the assistant falls back to its local rules.
    'enabled' => env('AI_PARSER_ENABLED', false),
    'provider' => env('AI_PROVIDER', 'groq'),

    'timeout' => env('AI_PARSER_TIMEOUT', 10),

    'studio' => [
        'enabled' => env('AI_STUDIO_ENABLED', true),
        'timeout' => env('AI_STUDIO_TIMEOUT', 60),
        'chat_timeout' => env('AI_STUDIO_CHAT_TIMEOUT', 12),
        'chat_fallback_timeout' => env('AI_STUDIO_CHAT_FALLBACK_TIMEOUT', 20),
        'video_timeout' => env('AI_STUDIO_VIDEO_TIMEOUT', 240),
        'text_model' => env('GROQ_MODEL', 'openai/gpt-oss-20b'),
        'image_model' => env('GEMINI_STUDIO_IMAGE_MODEL', 'gemini-3.1-flash-image'),
        'chat_model' => env('GROQ_MODEL', 'openai/gpt-oss-20b'),
        'video_model' => env('GEMINI_STUDIO_VIDEO_MODEL', 'gemini-omni-1.1-flash'),
    ],

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-3.8-flash'),
        'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
    ],
    'groq' => [
        'key' => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'openai/gpt-oss-20b'),
        'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
    ],

    'cloudflare' => [
        'account_id' => env('CLOUDFLARE_ACCOUNT_ID'),
        'api_token' => env('CLOUDFLARE_API_TOKEN'),
        'image_model' => env('CLOUDFLARE_AI_IMAGE_MODEL', '@cf/black-forest-labs/flux-1-schnell'),
        'base_url' => 'https://api.cloudflare.com/client/v4/accounts',
    ],
];
