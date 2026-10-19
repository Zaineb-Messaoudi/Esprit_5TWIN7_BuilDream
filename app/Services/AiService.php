<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    private string $provider;
    private string $apiKey;
    private string $baseUrl;
    private string $model;

    public function __construct()
    {
        $this->provider = config('ai.provider', 'openai');
        $this->apiKey = config('ai.api_key', '');
        $this->baseUrl = config('ai.base_url', '');
        $this->model = config('ai.model', 'gpt-4o-mini');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    public function chat(array $messages, array $options = []): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $response = match ($this->provider) {
            'openai' => $this->callOpenAI($messages, $options),
            'anthropic' => $this->callAnthropic($messages, $options),
            default => $this->callOpenAI($messages, $options),
        };

        return $response;
    }

    public function chatJson(array $messages, array $options = []): ?array
    {
        $response = $this->chat($messages, array_merge($options, ['response_format' => ['type' => 'json_object']]));
        
        if (!$response) {
            return null;
        }

        try {
            return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }
    }

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
            ])->timeout(30)->post($url, $payload);

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
            ])->timeout(30)->post($url, $payload);

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

    public function getEmbedding(string $text): ?array
    {
        if (!$this->isConfigured() || $this->provider !== 'openai') {
            return null;
        }

        try {
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
        } catch (\Throwable) {
            return null;
        }
    }
}