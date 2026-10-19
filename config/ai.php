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
    | Default Generation Options
    |--------------------------------------------------------------------------
    */
    'options' => [
        'temperature' => 0.3,
        'max_tokens' => 2048,
    ],
];