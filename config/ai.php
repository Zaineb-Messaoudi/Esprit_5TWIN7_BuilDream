<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Provider
    |--------------------------------------------------------------------------
    | Supported: 'ollama' (local, FREE), 'openai', 'anthropic'
    | Ollama runs locally on your machine - no API costs!
    | Install: https://ollama.com/
    */
    'provider' => env('AI_PROVIDER', 'ollama'),

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    | Required for OpenAI and Anthropic. NOT needed for Ollama.
    */
    'api_key' => env('AI_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    | - Ollama: http://localhost:11434 (default)
    | - OpenAI: https://api.openai.com/v1 (default)
    | - Anthropic: https://api.anthropic.com/v1 (default)
    | - OpenRouter: https://openrouter.ai/api/v1
    */
    'base_url' => env('AI_BASE_URL', 'http://localhost:11434'),

    /*
    |--------------------------------------------------------------------------
    | Chat Model
    |--------------------------------------------------------------------------
    | Ollama models (pull first: ollama pull <model>):
    | - llama3.2 (recommended, fast, 3B params)
    | - mistral (good quality)
    | - phi3 (Microsoft, small)
    | - qwen2.5 (multilingual)
    | - gemma2 (Google)
    | - llava (vision model for image validation)
    |
    | OpenAI: gpt-4o-mini, gpt-4o, gpt-3.5-turbo
    | Anthropic: claude-3-haiku, claude-3-sonnet
    */
    'model' => env('AI_MODEL', 'llama3.2'),

    /*
    |--------------------------------------------------------------------------
    | Vision Model (for image validation)
    |--------------------------------------------------------------------------
    | Ollama vision models (pull first):
    | - llava (recommended, general purpose)
    | - llava-llama3 (LLaVA with Llama 3)
    | - llava-mistral (LLaVA with Mistral)
    | - bakllava (BakLLaVA)
    |
    | OpenAI: gpt-4o-mini, gpt-4o (vision capable)
    */
    'vision_model' => env('AI_VISION_MODEL', 'llava'),

    /*
    |--------------------------------------------------------------------------
    | Embedding Model (for semantic search)
    |--------------------------------------------------------------------------
    | Ollama embedding models:
    | - nomic-embed-text (recommended, 768 dims)
    | - mxbai-embed-large (1024 dims)
    | - all-minilm (384 dims, fast)
    |
    | OpenAI: text-embedding-3-small (1536 dims)
    */
    'embedding_model' => env('AI_EMBEDDING_MODEL', 'nomic-embed-text'),

    /*
    |--------------------------------------------------------------------------
    | Fallback Provider Chain
    |--------------------------------------------------------------------------
    | Ordered list of providers to try when primary fails.
    | Each provider must have its configuration in 'providers' below.
    */
    'fallback_chain' => env('AI_FALLBACK_CHAIN', 'ollama,openai,anthropic'),

    /*
    |--------------------------------------------------------------------------
    | Provider Configurations (for fallback chain)
    |--------------------------------------------------------------------------
    | Each provider needs its own model, base_url, and api_key (if applicable).
    | These are used by chatWithFallback() when primary provider fails.
    */
    'providers' => [
        'ollama' => [
            'model' => env('AI_MODEL', 'llama3.2'),
            'vision_model' => env('AI_VISION_MODEL', 'llava'),
            'embedding_model' => env('AI_EMBEDDING_MODEL', 'nomic-embed-text'),
            'base_url' => env('AI_BASE_URL', 'http://localhost:11434'),
            'api_key' => null,
        ],
        'openai' => [
            'model' => env('AI_OPENAI_MODEL', 'gpt-4o-mini'),
            'vision_model' => env('AI_OPENAI_VISION_MODEL', 'gpt-4o-mini'),
            'embedding_model' => env('AI_OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
            'base_url' => env('AI_OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'api_key' => env('AI_OPENAI_API_KEY'),
        ],
        'anthropic' => [
            'model' => env('AI_ANTHROPIC_MODEL', 'claude-3-haiku'),
            'vision_model' => env('AI_ANTHROPIC_VISION_MODEL', 'claude-3-haiku'),
            'embedding_model' => null,
            'base_url' => env('AI_ANTHROPIC_BASE_URL', 'https://api.anthropic.com/v1'),
            'api_key' => env('AI_ANTHROPIC_API_KEY'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Circuit Breaker Settings
    |--------------------------------------------------------------------------
    */
    'circuit_breaker' => [
        'threshold' => env('AI_CB_THRESHOLD', 5),     // failures before opening
        'timeout' => env('AI_CB_TIMEOUT', 60),        // seconds before half-open
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Generation Options
    |--------------------------------------------------------------------------
    */
    'options' => [
        'temperature' => 0.3,
        'max_tokens' => 2048,
    ],
];