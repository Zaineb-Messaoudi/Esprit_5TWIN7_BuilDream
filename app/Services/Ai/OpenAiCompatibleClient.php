<?php

namespace App\Services\Ai;

use App\Contracts\AiClientInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiCompatibleClient implements AiClientInterface
{
    public function classify(string $prompt): string
    {
        $response = $this->client()->post($this->endpoint('/chat/completions'), [
            'model' => config('maintenance.ai.model'),
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'response_format' => ['type' => 'json_object'],
        ])->throw()->json('choices.0.message.content');

        return is_string($response) ? $response : throw new RuntimeException(__('Invalid AI response.'));
    }

    public function analyzeImage(string $absolutePath, string $mimeType): array
    {
        $image = base64_encode((string) file_get_contents($absolutePath));
        $content = $this->client()->post($this->endpoint('/chat/completions'), [
            'model' => config('maintenance.ai.model'),
            'messages' => [[
                'role' => 'user',
                'content' => [
                    ['type' => 'text', 'text' => 'Analyze this equipment image. Return strict JSON with damage_detected (boolean) and comments (concise French string).'],
                    ['type' => 'image_url', 'image_url' => ['url' => 'data:'.$mimeType.';base64,'.$image]],
                ],
            ]],
            'response_format' => ['type' => 'json_object'],
        ])->throw()->json('choices.0.message.content');

        if (! is_string($content)) {
            throw new RuntimeException(__('Invalid AI response.'));
        }
        $decoded = json_decode($content, true);

        return is_array($decoded) ? $decoded : throw new RuntimeException(__('Invalid AI response.'));
    }

    public function transcribe(UploadedFile $audio): string
    {
        $response = $this->client()->attach('file', $audio->getContent(), $audio->getClientOriginalName())
            ->post($this->endpoint('/audio/transcriptions'), ['model' => config('maintenance.ai.transcription_model')])
            ->throw()->json('text');

        return is_string($response) ? $response : throw new RuntimeException(__('Invalid AI response.'));
    }

    private function client(): \Illuminate\Http\Client\PendingRequest
    {
        $key = (string) config('maintenance.ai.api_key');
        if ($key === '') {
            throw new RuntimeException(__('AI service is not configured.'));
        }

        return Http::withToken($key)
            ->acceptJson()
            ->connectTimeout(15)
            // One browser action must remain below PHP's execution limit. Retrying a
            // slow vision request here can otherwise make the request exceed 120 seconds.
            ->timeout((int) config('maintenance.ai.timeout', 110));
    }

    private function endpoint(string $path): string
    {
        return rtrim((string) config('maintenance.ai.base_url'), '/').$path;
    }
}
