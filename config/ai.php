<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Provider
    |--------------------------------------------------------------------------
    | Supported: 'ollama' (local, FREE), 'groq', 'openai', 'anthropic'
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
    'api_key' => env('AI_API_KEY') ?: env('GROQ_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    | - Ollama: http://localhost:11434 (default)
    | - OpenAI: https://api.openai.com/v1 (default)
    | - Anthropic: https://api.anthropic.com/v1 (default)
    | - OpenRouter: https://openrouter.ai/api/v1
    */
    'base_url' => env('AI_BASE_URL') ?: (env('AI_PROVIDER', 'ollama') === 'groq'
        ? env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1')
        : 'http://localhost:11434'),

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
    'model' => env('AI_MODEL') ?: (env('AI_PROVIDER', 'ollama') === 'groq'
        ? env('GROQ_MODEL', 'openai/gpt-oss-20b')
        : 'llama3.2'),

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
    'fallback_chain' => env('AI_FALLBACK_CHAIN', 'ollama,groq,openai,anthropic'),

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
        'groq' => [
            'model' => env('GROQ_MODEL', 'openai/gpt-oss-20b'),
            'vision_model' => env('GROQ_VISION_MODEL', 'meta-llama/llama-4-scout-17b-16e-instruct'),
            'embedding_model' => null,
            'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
            'api_key' => env('GROQ_API_KEY'),
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
    | Feature Flag
    |--------------------------------------------------------------------------
    | Enable/disable AI features globally without code changes.
    */
    'enabled' => env('AI_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Default Generation Options
    |--------------------------------------------------------------------------
    */
    'options' => [
        'temperature' => 0.3,
        'max_tokens' => 2048,
    ],

    /*
    |--------------------------------------------------------------------------
    | Assistant Parser (Groq / Gemini)
    |--------------------------------------------------------------------------
    | Settings for the assistant parser. These used to be the top-level
    | 'enabled', 'provider' and 'timeout' keys, which clash with the global
    | AI settings above, so they now live under 'parser'.
    */
    'parser' => [
        // Off by default: the assistant falls back to its local rules.
        'enabled' => env('AI_PARSER_ENABLED', false),
        'provider' => env('AI_PARSER_PROVIDER', 'groq'),
        'timeout' => env('AI_PARSER_TIMEOUT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Studio
    |--------------------------------------------------------------------------
    */
    'studio' => [
        'enabled' => env('AI_STUDIO_ENABLED', true),
        // Reuse the maintenance timeout unless Studio explicitly overrides it.
        'timeout' => env('AI_STUDIO_TIMEOUT', env('MAINTENANCE_AI_TIMEOUT', 60)),
        'chat_timeout' => env('AI_STUDIO_CHAT_TIMEOUT', 12),
        'chat_fallback_timeout' => env('AI_STUDIO_CHAT_FALLBACK_TIMEOUT', 20),
        // Maintenance and Studio share the Gemini text model by default.
        'text_model' => env('GEMINI_STUDIO_TEXT_MODEL', env('MAINTENANCE_AI_MODEL', env('GEMINI_MODEL', 'gemini-3.8-flash'))),
        'chat_model' => env('GEMINI_STUDIO_CHAT_MODEL', env('MAINTENANCE_AI_MODEL', env('GEMINI_MODEL', 'gemini-3.8-flash'))),
    ],

    /*
    |--------------------------------------------------------------------------
    | Gemini / Groq (used by the parser and AI Studio)
    |--------------------------------------------------------------------------
    */
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

];
