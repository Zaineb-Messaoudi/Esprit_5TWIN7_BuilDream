<?php

namespace App\Services;

use App\Exceptions\GroqException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Small client for the Groq API, shared by every AI feature of the module.
 * Groq is compatible with the OpenAI protocol: one POST to /chat/completions.
 *
 * What it does for you:
 *  - reads the key and the settings from config/groq.php (never from the code)
 *  - uses a timeout, so a slow service cannot freeze a page
 *  - turns every failure into a GroqException with a friendly message
 *  - never writes the key in the logs
 */
class GroqClient
{
    /** True when a key is present in the .env file. */
    public function isConfigured(): bool
    {
        return filled(config('groq.key'));
    }

    /**
     * Sends a conversation to the model and returns the text of its answer.
     *
     * @param  array<int, array{role: string, content: string}>  $messages  e.g. [['role' => 'user', 'content' => '...']]
     * @param  array{model?: string, temperature?: float, max_tokens?: int, json?: bool, reasoning_effort?: string}  $options
     *                    json = true asks for a JSON answer (the prompt must also mention "JSON")
     *
     * @throws GroqException
     */
    public function chat(array $messages, array $options = []): string
    {
        if (! $this->isConfigured()) {
            throw new GroqException('not_configured');
        }

        $model = $options['model'] ?? config('groq.model');

        $payload = [
            'model'                 => $model,
            'messages'              => $messages,
            'temperature'           => $options['temperature'] ?? 0.2, // low = steady, factual answers
            'max_completion_tokens' => $options['max_tokens'] ?? config('groq.max_completion_tokens'),
        ];

        // Only the gpt-oss models understand "reasoning_effort"
        if (str_starts_with((string) $model, 'openai/gpt-oss')) {
            $payload['reasoning_effort'] = $options['reasoning_effort'] ?? config('groq.reasoning_effort');
        }

        if (! empty($options['json'])) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        try {
            $response = Http::withToken(config('groq.key'))
                ->acceptJson()
                ->timeout(config('groq.timeout'))
                ->post(rtrim(config('groq.base_url'), '/') . '/chat/completions', $payload);
        } catch (ConnectionException $e) {
            Log::warning('Groq unreachable: ' . $e->getMessage());
            throw new GroqException('network');
        }

        // 429 = quota reached (requests or tokens per minute / per day)
        if ($response->status() === 429) {
            throw new GroqException('rate_limited');
        }

        if ($response->failed()) {
            // The body explains the error; it never contains our key
            Log::warning('Groq request failed', [
                'status' => $response->status(),
                'body'   => mb_substr($response->body(), 0, 500),
            ]);
            throw new GroqException('http_error', 'HTTP ' . $response->status());
        }

        $content = data_get($response->json(), 'choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            throw new GroqException('empty_answer');
        }

        return trim($content);
    }
}