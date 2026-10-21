<?php

/*
|--------------------------------------------------------------------------
| Groq (AI features of the rental module)
|--------------------------------------------------------------------------
| Laravel loads every file of the config folder automatically.
| The values come from the .env file (never commit your key):
|
|   GROQ_API_KEY=your_key
|   GROQ_MODEL=openai/gpt-oss-120b      (optional)
|
| After changing .env run:  php artisan config:clear
*/

return [
    // Secret key of your Groq account. It lives only in .env.
    'key' => env('GROQ_API_KEY'),

    // Groq speaks the OpenAI "chat completions" protocol.
    'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),

    // Model used by default (the free tier allows 30 requests / minute, 8 000 tokens / minute).
    'model' => env('GROQ_MODEL', 'openai/gpt-oss-120b'),

    // Seconds to wait for Groq before giving up (the page then shows a friendly message).
    'timeout' => (int) env('GROQ_TIMEOUT', 25),

    // Maximum size of an answer. For gpt-oss models the "thinking" tokens count too,
    // so keep a comfortable margin.
    'max_completion_tokens' => (int) env('GROQ_MAX_TOKENS', 1500),

    // gpt-oss models only: how long the model thinks (low / medium / high).
    // "low" keeps the answers fast and saves the token quota.
    'reasoning_effort' => env('GROQ_REASONING_EFFORT', 'low'),

    // How long a generated summary is kept in the cache (the contract text rarely changes).
    'cache_days' => (int) env('GROQ_CACHE_DAYS', 30),
];
