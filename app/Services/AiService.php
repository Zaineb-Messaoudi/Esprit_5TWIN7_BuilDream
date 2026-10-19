<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AI Service supporting multiple LLM providers:
 * - OpenAI (cloud, paid)
 * - Anthropic (cloud, paid)
 * - Ollama (local, FREE - runs on your machine)
 * 
 * Configuration via config/ai.php and .env:
 * - AI_PROVIDER=ollama|openai|anthropic
 * - AI_BASE_URL (for Ollama: http://localhost:11434)
 * - AI_MODEL (e.g., llama3.2, mistral, phi3 for Ollama)
 * - AI_API_KEY (not needed for Ollama)
 */
class AiService
{
    private string $provider;
    private ?string $apiKey;
    private string $baseUrl;
    private string $model;
    private string $visionModel;

    public function __construct()
    {
        $this->provider = config('ai.provider', 'ollama');
        $this->apiKey = config('ai.api_key');
        $this->baseUrl = config('ai.base_url', '');
        $this->model = config('ai.model', 'llama3.2');
        $this->visionModel = config('ai.vision_model', 'llava');
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    /**
     * Check if the AI service is properly configured.
     * For Ollama, no API key is required - just needs base URL.
     */
    public function isConfigured(): bool
    {
        if ($this->provider === 'ollama') {
            return !empty($this->baseUrl);
        }
        return !empty($this->apiKey);
    }

    /**
     * Send a chat completion request.
     * Returns the assistant's response text or null on failure.
     */
    public function chat(array $messages, array $options = []): ?string
    {
        if (!$this->isConfigured()) {
            Log::warning('AI service not configured', ['provider' => $this->provider]);
            return null;
        }

        $hasImages = $this->hasImages($messages);

        $response = match ($this->provider) {
            'openai' => $this->callOpenAI($messages, $options),
            'anthropic' => $this->callAnthropic($messages, $options),
            'ollama' => $hasImages ? $this->callOllamaVision($messages, $options) : $this->callOllama($messages, $options),
            default => $hasImages ? $this->callOllamaVision($messages, $options) : $this->callOllama($messages, $options),
        };

        return $response;
    }

    /**
     * Check if messages contain image content.
     */
    private function hasImages(array $messages): bool
    {
        foreach ($messages as $message) {
            if (is_array($message['content'] ?? null)) {
                foreach ($message['content'] as $part) {
                    if (isset($part['type']) && $part['type'] === 'image_url') {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Send a chat completion request expecting JSON response.
     * Adds response_format for providers that support it.
     */
    public function chatJson(array $messages, array $options = []): ?array
    {
        // Only OpenAI supports response_format natively
        $formatOptions = ($this->provider === 'openai') 
            ? ['response_format' => ['type' => 'json_object']] 
            : [];

        $response = $this->chat($messages, array_merge($options, $formatOptions));
        
        if (!$response) {
            return null;
        }

        try {
            return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            Log::warning('Failed to parse AI JSON response', [
                'provider' => $this->provider,
                'response' => substr($response, 0, 500),
                'error' => $e->getMessage(),
            ]);
            // Try to extract JSON from markdown code blocks
            if (preg_match('/```json\s*(.*?)\s*```/s', $response, $matches)) {
                try {
                    return json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException) {
                    // Fall through to null
                }
            }
            return null;
        }
    }

    /**
     * Call OpenAI-compatible API (OpenAI, OpenRouter, etc.)
     */
    private function callOpenAI(array $messages, array $options): ?string
    {
        $url = $this->baseUrl ?: 'https://api.openai.com/v1/chat/completions';
        
        $payload = array_merge([
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => 0.3,
            'max_tokens' => 2048,
        ], $options);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($url, $payload);

            if ($response->failed()) {
                Log::warning('OpenAI API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $response->json('choices.0.message.content');
        } catch (\Throwable $e) {
            Log::warning('OpenAI request failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Call Anthropic API (Claude)
     */
    private function callAnthropic(array $messages, array $options): ?string
    {
        $url = $this->baseUrl ?: 'https://api.anthropic.com/v1/messages';
        
        $systemMessage = '';
        $userMessages = [];
        
        foreach ($messages as $message) {
            if ($message['role'] === 'system') {
                $systemMessage = $message['content'];
            } else {
                $userMessages[] = $message;
            }
        }

        $payload = array_merge([
            'model' => $this->model,
            'system' => $systemMessage,
            'messages' => $userMessages,
            'max_tokens' => $options['max_tokens'] ?? 2048,
            'temperature' => $options['temperature'] ?? 0.3,
        ], array_diff_key($options, ['max_tokens' => '', 'temperature' => '', 'response_format' => '']));

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($url, $payload);

            if ($response->failed()) {
                Log::warning('Anthropic API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $response->json('content.0.text');
        } catch (\Throwable $e) {
            Log::warning('Anthropic request failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Call Ollama local API (FREE - runs on your machine)
     * 
     * Ollama API: http://localhost:11434/api/chat
     * Models: llama3.2, mistral, phi3, qwen2.5, gemma2, etc.
     * Install: curl -fsSL https://ollama.com/install.sh | sh
     * Pull model: ollama pull llama3.2
     */
    private function callOllama(array $messages, array $options): ?string
    {
        $url = rtrim($this->baseUrl, '/') . '/api/chat';
        
        // Convert messages to Ollama format
        $ollamaMessages = [];
        foreach ($messages as $message) {
            $ollamaMessages[] = [
                'role' => $message['role'],
                'content' => $message['content'],
            ];
        }

        $payload = array_merge([
            'model' => $this->model,
            'messages' => $ollamaMessages,
            'stream' => false,
            'options' => [
                'temperature' => $options['temperature'] ?? 0.3,
                'num_predict' => $options['max_tokens'] ?? 2048,
            ],
        ], array_diff_key($options, ['temperature' => '', 'max_tokens' => '', 'response_format' => '']));

        try {
            $response = Http::timeout(120)->post($url, $payload);

            if ($response->failed()) {
                Log::warning('Ollama API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'url' => $url,
                    'model' => $this->model,
                ]);
                return null;
            }

            // Ollama returns: { "message": { "role": "assistant", "content": "..." } }
            return $response->json('message.content');
        } catch (\Throwable $e) {
            Log::warning('Ollama request failed', [
                'error' => $e->getMessage(),
                'url' => $url,
                'model' => $this->model,
            ]);
            return null;
        }
    }

    /**
     * Get text embeddings for semantic search.
     * 
     * Note: Ollama supports embeddings via /api/embeddings with models like:
     * - nomic-embed-text (recommended)
     * - mxbai-embed-large
     * - all-minilm
     * 
     * For OpenAI, uses text-embedding-3-small.
     * Results are cached for 24 hours to avoid repeated API calls.
     */
    public function getEmbedding(string $text): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $cacheKey = 'ai_embedding_' . md5($text . '|' . $this->provider . '|' . $this->model);
        
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 86400, function () use ($text) {
            try {
                if ($this->provider === 'ollama') {
                    return $this->getOllamaEmbedding($text);
                } elseif ($this->provider === 'openai') {
                    return $this->getOpenAIEmbedding($text);
                }
                return null;
            } catch (\Throwable $e) {
                Log::warning('Embedding request failed', [
                    'provider' => $this->provider,
                    'error' => $e->getMessage(),
                ]);
                return null;
            }
        });
    }

    /**
     * Get embeddings from Ollama.
     */
    private function getOllamaEmbedding(string $text): ?array
    {
        $url = rtrim($this->baseUrl, '/') . '/api/embeddings';
        
        // Use a dedicated embedding model if configured, otherwise use the chat model
        $embeddingModel = config('ai.embedding_model', 'nomic-embed-text');
        
        $response = Http::timeout(60)->post($url, [
            'model' => $embeddingModel,
            'prompt' => $text,
        ]);

        if ($response->failed()) {
            Log::warning('Ollama embedding error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'model' => $embeddingModel,
            ]);
            return null;
        }

        // Ollama returns: { "embedding": [...] }
        return $response->json('embedding');
    }

    /**
     * Get embeddings from OpenAI.
     */
    private function getOpenAIEmbedding(string $text): ?array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post('https://api.openai.com/v1/embeddings', [
            'model' => 'text-embedding-3-small',
            'input' => $text,
        ]);

        if ($response->failed()) {
            return null;
        }

        return $response->json('data.0.embedding');
    }

    /**
     * Call Ollama vision model (LLaVA) for image validation.
     * 
     * Requires a vision model like llava, llava-llama3, llava-mistral, etc.
     * Pull first: ollama pull llava
     */
    private function callOllamaVision(array $messages, array $options): ?string
    {
        $url = rtrim($this->baseUrl, '/') . '/api/chat';
        
        $ollamaMessages = [];
        foreach ($messages as $message) {
            if (is_array($message['content'])) {
                // Handle vision content (text + image)
                $content = '';
                $images = [];
                foreach ($message['content'] as $part) {
                    if ($part['type'] === 'text') {
                        $content = $part['text'];
                    } elseif ($part['type'] === 'image_url') {
                        // Extract base64 from data URL
                        if (preg_match('/^data:image\/[^;]+;base64,(.+)$/', $part['image_url']['url'], $matches)) {
                            $images[] = $matches[1];
                        }
                    }
                }
                $ollamaMessages[] = [
                    'role' => $message['role'],
                    'content' => $content,
                    'images' => $images,
                ];
            } else {
                $ollamaMessages[] = [
                    'role' => $message['role'],
                    'content' => $message['content'],
                ];
            }
        }

        // Use vision model for Ollama, fallback to chat model
        $visionModel = $this->visionModel ?: $this->model;

        $payload = array_merge([
            'model' => $visionModel,
            'messages' => $ollamaMessages,
            'stream' => false,
            'options' => [
                'temperature' => $options['temperature'] ?? 0.3,
                'num_predict' => $options['max_tokens'] ?? 2048,
            ],
        ], array_diff_key($options, ['temperature' => '', 'max_tokens' => '', 'response_format' => '']));

        try {
            $response = Http::timeout(120)->post($url, $payload);

            if ($response->failed()) {
                Log::warning('Ollama vision API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'url' => $url,
                    'model' => $visionModel,
                ]);
                return null;
            }

            return $response->json('message.content');
        } catch (\Throwable $e) {
            Log::warning('Ollama vision request failed', [
                'error' => $e->getMessage(),
                'url' => $url,
                'model' => $visionModel,
            ]);
            return null;
        }
    }

    /**
     * Check if Ollama is running and list available models.
     * Useful for health checks in admin UI.
     */
    public function checkOllamaHealth(): array
    {
        if ($this->provider !== 'ollama') {
            return ['available' => false, 'reason' => 'Provider is not Ollama'];
        }

        try {
            $url = rtrim($this->baseUrl, '/') . '/api/tags';
            $response = Http::timeout(10)->get($url);

            if ($response->failed()) {
                return ['available' => false, 'reason' => 'Ollama API unreachable'];
            }

            $models = collect($response->json('models', []))->pluck('name')->toArray();
            
            return [
                'available' => true,
                'models' => $models,
                'current_model' => $this->model,
                'model_exists' => in_array($this->model, $models),
                'vision_models' => array_filter($models, fn ($m) => stripos($m, 'llava') !== false || stripos($m, 'vision') !== false || stripos($m, 'bakllava') !== false),
            ];
        } catch (\Throwable $e) {
            return ['available' => false, 'reason' => $e->getMessage()];
        }
    }

    /**
     * Calculate cosine similarity between two vectors.
     * Works with JSON-stored embeddings on MySQL/PostgreSQL.
     */
    public function cosineSimilarity(array $a, array $b): float
    {
        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;
        $count = min(count($a), count($b));

        for ($i = 0; $i < $count; $i++) {
            $dot += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }

        if ($normA === 0.0 || $normB === 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }

    /**
     * Semantic search using JSON embeddings (MySQL-compatible).
     * Fetches all embeddings, computes similarity in PHP.
     * For production with >10k rows, use pgvector or Qdrant.
     */
    public function semanticSearchJson(string $query, int $limit = 10): ?array
    {
        $queryEmbedding = $this->getEmbedding($query);
        if (!$queryEmbedding) {
            return null;
        }

        $equipment = Equipment::whereNotNull('embedding')
            ->where('approval_status', 'published')
            ->where('status', 'available')
            ->with(['category', 'energyProfile'])
            ->get(['id', 'name', 'brand', 'model', 'price_per_day', 'location', 'category_id', 'embedding']);

        if ($equipment->isEmpty()) {
            return ['results' => []];
        }

        $results = $equipment->map(function ($e) use ($queryEmbedding) {
            $embedding = is_array($e->embedding) ? $e->embedding : json_decode($e->embedding, true);
            if (!$embedding) {
                return null;
            }
            $score = $this->cosineSimilarity($queryEmbedding, $embedding);
            return [
                'id' => $e->id,
                'name' => $e->name,
                'brand' => $e->brand,
                'model' => $e->model,
                'price_per_day' => $e->price_per_day,
                'location' => $e->location,
                'category' => $e->category?->name,
                'energyProfile' => $e->energyProfile,
                'similarity' => $score,
            ];
        })->filter()->sortByDesc('similarity')->take($limit)->values()->toArray();

        return ['results' => $results];
    }
}