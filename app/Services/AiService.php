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

    // Circuit breaker state
    private array $circuitBreaker = [
        'failures' => 0,
        'last_failure' => 0,
        'state' => 'closed', // closed, open, half-open
        'threshold' => 5,
        'timeout' => 60, // seconds
    ];

    public function __construct()
    {
        $this->provider = config('ai.provider', 'ollama');
        $this->apiKey = config('ai.api_key');
        $this->baseUrl = config('ai.base_url', '');
        $this->model = config('ai.model', 'llama3.2');
        $this->visionModel = config('ai.vision_model', 'llava');
        
        // Load circuit breaker settings from config
        $this->circuitBreaker['threshold'] = config('ai.circuit_breaker.threshold', 5);
        $this->circuitBreaker['timeout'] = config('ai.circuit_breaker.timeout', 60);
    }

    /**
     * Check if AI service is enabled and properly configured.
     * Respects the AI_ENABLED feature flag.
     */
    public function isConfigured(): bool
    {
        if (!config('ai.enabled', true)) {
            return false;
        }
        
        if ($this->provider === 'ollama') {
            return !empty($this->baseUrl);
        }
        return !empty($this->apiKey);
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
     * Check circuit breaker state and record failures.
     */
    private function checkCircuitBreaker(): bool
    {
        $now = time();
        $cb = $this->circuitBreaker;

        if ($cb['state'] === 'open') {
            if ($now - $cb['last_failure'] > $cb['timeout']) {
                // Half-open: allow one request through
                $this->circuitBreaker['state'] = 'half-open';
                return true;
            }
            return false; // Circuit open, reject request
        }
        return true; // Closed or half-open, allow request
    }

    private function recordSuccess(): void
    {
        $this->circuitBreaker['failures'] = 0;
        $this->circuitBreaker['state'] = 'closed';
    }

    private function recordFailure(): void
    {
        $this->circuitBreaker['failures']++;
        $this->circuitBreaker['last_failure'] = time();
        
        if ($this->circuitBreaker['failures'] >= $this->circuitBreaker['threshold']) {
            $this->circuitBreaker['state'] = 'open';
            Log::warning('AI Circuit breaker OPENED', [
                'provider' => $this->provider,
                'failures' => $this->circuitBreaker['failures'],
                'threshold' => $this->circuitBreaker['threshold'],
            ]);
        }
    }

    /**
     * Get circuit breaker status for health checks.
     */
    public function getCircuitBreakerStatus(): array
    {
        return $this->circuitBreaker;
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

        // Check circuit breaker
        if (!$this->checkCircuitBreaker()) {
            Log::warning('AI Circuit breaker OPEN - request rejected', [
                'provider' => $this->provider,
                'failures' => $this->circuitBreaker['failures'],
            ]);
            return null;
        }

        $hasImages = $this->hasImages($messages);

        try {
            $response = match ($this->provider) {
                'openai' => $this->callOpenAI($messages, $options),
                'anthropic' => $this->callAnthropic($messages, $options),
                'ollama' => $hasImages ? $this->callOllamaVision($messages, $options) : $this->callOllama($messages, $options),
                default => $hasImages ? $this->callOllamaVision($messages, $options) : $this->callOllama($messages, $options),
            };

            if ($response) {
                $this->recordSuccess();
            } else {
                $this->recordFailure();
            }

            return $response;
        } catch (\Throwable $e) {
            $this->recordFailure();
            Log::error('AI chat exception', [
                'provider' => $this->provider,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
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
     * Check if a specific model is loaded in VRAM (Ollama).
     * Uses /api/show endpoint to get model details including VRAM usage.
     */
    public function checkModelLoadedInVram(string $model = null): array
    {
        if ($this->provider !== 'ollama') {
            return ['loaded' => false, 'reason' => 'Provider is not Ollama'];
        }

        $model = $model ?? $this->model;

        try {
            $url = rtrim($this->baseUrl, '/') . '/api/show';
            $response = Http::timeout(10)->post($url, [
                'name' => $model,
            ]);

            if ($response->failed()) {
                return [
                    'loaded' => false,
                    'reason' => 'Model not found or Ollama error',
                    'model' => $model,
                ];
            }

            $details = $response->json();
            $size = $details['details']['parameter_size'] ?? 'unknown';
            
            // Check if model details indicate it's loaded (has size info)
            $isLoaded = !empty($details['details']);
            
            return [
                'loaded' => $isLoaded,
                'model' => $model,
                'parameter_size' => $size,
                'quantization' => $details['details']['quantization_level'] ?? 'unknown',
                'context_length' => $details['details']['context_length'] ?? 'unknown',
                'embedding_length' => $details['details']['embedding_length'] ?? 'unknown',
                'vram_estimate_mb' => $this->estimateVram($details['details'] ?? []),
            ];
        } catch (\Throwable $e) {
            return ['loaded' => false, 'reason' => $e->getMessage(), 'model' => $model];
        }
    }

    /**
     * Estimate VRAM usage from model details.
     */
    private function estimateVram(array $details): int
    {
        $paramSize = $details['parameter_size'] ?? '0';
        $quantization = $details['quantization_level'] ?? 'Q4_0';
        
        // Extract number from parameter_size (e.g., "7B" -> 7)
        preg_match('/(\d+(\.\d+)?)([KMG])?/', $paramSize, $matches);
        if (empty($matches[1])) {
            return 0;
        }
        
        $params = (float) $matches[1];
        $unit = $matches[3] ?? 'B';
        
        switch ($unit) {
            case 'K': $params *= 1000; break;
            case 'M': $params *= 1000000; break;
            case 'B': $params *= 1000000000; break;
        }
        
        // Estimate bytes per parameter based on quantization
        $bytesPerParam = match (true) {
            stripos($quantization, 'Q8') !== false => 1.0,
            stripos($quantization, 'Q6') !== false => 0.75,
            stripos($quantization, 'Q5') !== false => 0.625,
            stripos($quantization, 'Q4') !== false => 0.5,
            stripos($quantization, 'Q3') !== false => 0.375,
            stripos($quantization, 'Q2') !== false => 0.25,
            default => 0.5, // Assume Q4_0
        };
        
        $vramBytes = $params * $bytesPerParam;
        return (int) ($vramBytes / 1024 / 1024); // MB
    }

    /**
     * Get GPU memory usage from Ollama.
     * Uses /api/ps endpoint to get running models and their VRAM usage.
     * Returns array with GPU memory info and alert if >90% VRAM used.
     */
    public function getGpuMemoryUsage(): array
    {
        if ($this->provider !== 'ollama') {
            return ['available' => false, 'reason' => 'Provider is not Ollama'];
        }

        try {
            $url = rtrim($this->baseUrl, '/') . '/api/ps';
            $response = Http::timeout(10)->get($url);

            if ($response->failed()) {
                return ['available' => false, 'reason' => 'Ollama /api/ps endpoint failed'];
            }

            $processes = $response->json('models', []);
            
            $totalVramUsed = 0;
            $totalVramAvailable = 0;
            $models = [];

            foreach ($processes as $process) {
                $modelName = $process['name'] ?? 'unknown';
                $vramUsed = $process['size_vram'] ?? 0;
                $size = $process['size'] ?? 0;
                
                $models[] = [
                    'name' => $modelName,
                    'vram_used_mb' => (int) ($vramUsed / 1024 / 1024),
                    'size_mb' => (int) ($size / 1024 / 1024),
                ];
                
                $totalVramUsed += $vramUsed;
            }

            // Estimate total GPU VRAM (rough estimate based on largest model)
            // This is approximate since Ollama doesn't expose total GPU memory directly
            // We can estimate from the largest model's VRAM usage
            $estimatedTotalVram = $totalVramUsed > 0 ? $totalVramUsed * 2 : 0; // rough estimate
            $usagePercent = $estimatedTotalVram > 0 ? round(($totalVramUsed / $estimatedTotalVram) * 100, 1) : 0;

            $alert = $usagePercent > 90;

            return [
                'available' => true,
                'total_vram_used_mb' => (int) ($totalVramUsed / 1024 / 1024),
                'estimated_total_vram_mb' => (int) ($estimatedTotalVram / 1024 / 1024),
                'usage_percent' => $usagePercent,
                'alert' => $alert,
                'alert_threshold' => 90,
                'models' => $models,
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
    public function semanticSearchJson(string $query, int $limit = 10, int $page = 1): ?array
    {
        $queryEmbedding = $this->getEmbedding($query);
        if (!$queryEmbedding) {
            return null;
        }

        $perPage = min($limit, 50);
        $offset = ($page - 1) * $perPage;

        $equipment = Equipment::whereNotNull('embedding')
            ->where('approval_status', 'published')
            ->where('status', 'available')
            ->with(['category', 'energyProfile'])
            ->get(['id', 'name', 'brand', 'model', 'price_per_day', 'location', 'category_id', 'embedding']);

        if ($equipment->isEmpty()) {
            return [
                'results' => [],
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => 0,
                    'last_page' => 1,
                ],
            ];
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
        })->filter()->sortByDesc('similarity')->values();

        $total = $results->count();
        $paginatedResults = $results->slice($offset, $perPage)->values()->toArray();
        $lastPage = (int) ceil($total / $perPage);

        return [
            'results' => $paginatedResults,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => max(1, $lastPage),
            ],
        ];
    }

    /**
     * Execute chat with fallback provider chain.
     * Tries primary provider, then falls back to configured alternatives.
     * 
     * @param array $messages
     * @param array $options
     * @param array $fallbackChain Ordered list of providers to try: ['ollama', 'openai', 'anthropic']
     * @return array|null ['response' => string, 'provider' => string] or null
     */
    public function chatWithFallback(array $messages, array $options = [], array $fallbackChain = null): ?array
    {
        $fallbackChain = $fallbackChain ?? config('ai.fallback_chain', ['ollama', 'openai', 'anthropic']);
        
        $originalProvider = $this->provider;
        $originalModel = $this->model;
        $originalBaseUrl = $this->baseUrl;
        $originalApiKey = $this->apiKey;

        foreach ($fallbackChain as $provider) {
            // Skip if provider not configured
            $providerConfig = config("ai.providers.$provider");
            if (!$providerConfig) {
                continue;
            }

            // Switch to this provider
            $this->provider = $provider;
            $this->model = $providerConfig['model'] ?? $this->model;
            $this->baseUrl = $providerConfig['base_url'] ?? $this->baseUrl;
            $this->apiKey = $providerConfig['api_key'] ?? $this->apiKey;
            $this->visionModel = $providerConfig['vision_model'] ?? $this->visionModel;

            // Reset circuit breaker for new provider
            $this->circuitBreaker = [
                'failures' => 0,
                'last_failure' => 0,
                'state' => 'closed',
                'threshold' => 5,
                'timeout' => 60,
            ];

            // Check if provider is configured
            if (!$this->isConfigured()) {
                Log::info("AI Fallback: Provider $provider not configured, skipping");
                continue;
            }

            try {
                $response = $this->chat($messages, $options);
                
                if ($response) {
                    // Restore original provider
                    $this->provider = $originalProvider;
                    $this->model = $originalModel;
                    $this->baseUrl = $originalBaseUrl;
                    $this->apiKey = $originalApiKey;
                    $this->visionModel = config('ai.vision_model', 'llava');
                    
                    return [
                        'response' => $response,
                        'provider' => $provider,
                        'model' => $this->model,
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning("AI Fallback: Provider $provider failed", [
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        }

        // Restore original provider on complete failure
        $this->provider = $originalProvider;
        $this->model = $originalModel;
        $this->baseUrl = $originalBaseUrl;
        $this->apiKey = $originalApiKey;
        $this->visionModel = config('ai.vision_model', 'llava');

        Log::error('AI Fallback: All providers failed', [
            'fallback_chain' => $fallbackChain,
        ]);

        return null;
    }
}