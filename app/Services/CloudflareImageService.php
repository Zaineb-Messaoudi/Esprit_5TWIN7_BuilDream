<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class CloudflareImageService
{
    /** @return array{text: string, media: string, mime_type: string, model: string, input_tokens: null, output_tokens: null} */
    public function generate(string $prompt): array
    {
        $accountId = (string) config('ai.cloudflare.account_id');
        $token = (string) config('ai.cloudflare.api_token');
        $model = (string) config('ai.cloudflare.image_model');

        if ($accountId === '' || $token === '') {
            throw new RuntimeException(__('Cloudflare Workers AI is not configured. Add its account ID and API token to the server environment.'));
        }

        $response = Http::timeout((int) config('ai.studio.timeout', 60))
            ->connectTimeout(10)
            ->withToken($token)
            ->accept('application/json, image/jpeg')
            ->post(rtrim((string) config('ai.cloudflare.base_url'), '/').'/'.$accountId.'/ai/run/'.$model, [
                'prompt' => $prompt,
                'steps' => 4,
            ]);

        if ($response->failed()) {
            $status = $response->status();
            $message = match ($status) {
                401, 403 => __('Cloudflare rejected the Workers AI token or account permissions.'),
                429 => __('The free Cloudflare image-generation limit was reached. Try again after the daily reset.'),
                default => __('Cloudflare image generation failed with status :status.', ['status' => $status]),
            };

            throw new CloudflareImageException($message, $status);
        }

        $mimeType = $response->header('Content-Type', '');
        $bytes = $response->body();

        if (str_contains(strtolower($mimeType), 'json')) {
            $image = data_get($response->json(), 'result.image') ?? data_get($response->json(), 'image');
            $decoded = is_string($image) ? base64_decode($image, true) : false;
            if (! is_string($decoded)) {
                throw new RuntimeException(__('Cloudflare returned no valid image.'));
            }
            $bytes = $decoded;
            $mimeType = 'image/jpeg';
        }

        if ($bytes === '' || ! str_starts_with(strtolower($mimeType), 'image/')) {
            throw new RuntimeException(__('Cloudflare returned no valid image.'));
        }

        return [
            'text' => __('Image generated successfully.'),
            'media' => $bytes,
            'mime_type' => strtolower(trim(explode(';', $mimeType)[0])),
            'model' => $model,
            'input_tokens' => null,
            'output_tokens' => null,
        ];
    }
}

class CloudflareImageException extends RuntimeException
{
    public function __construct(string $message, public readonly int $status)
    {
        parent::__construct($message);
    }
}
