<?php

namespace App\Services\Maintenance;

use App\Contracts\AiClientInterface;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class AudioTranscriptionService
{
    public function __construct(private readonly AiClientInterface $client) {}

    public function transcribe(UploadedFile $audio): string
    {
        $text = trim($this->client->transcribe($audio));

        return $text !== '' ? $text : throw new RuntimeException(__('Invalid AI response.'));
    }
}
