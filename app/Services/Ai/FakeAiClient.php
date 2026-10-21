<?php

namespace App\Services\Ai;

use App\Contracts\AiClientInterface;
use Illuminate\Http\UploadedFile;

class FakeAiClient implements AiClientInterface
{
    public function __construct(public string $classification = '{}', public array $vision = [], public string $transcription = '') {}

    public function classify(string $prompt): string
    {
        return $this->classification;
    }

    public function analyzeImage(string $absolutePath, string $mimeType): array
    {
        return $this->vision;
    }

    public function transcribe(UploadedFile $audio): string
    {
        return $this->transcription;
    }
}
