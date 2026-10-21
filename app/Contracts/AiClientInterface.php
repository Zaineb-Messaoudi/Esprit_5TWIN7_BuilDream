<?php

namespace App\Contracts;

use Illuminate\Http\UploadedFile;

interface AiClientInterface
{
    public function classify(string $prompt): string;

    public function analyzeImage(string $absolutePath, string $mimeType): array;

    public function transcribe(UploadedFile $audio): string;
}
