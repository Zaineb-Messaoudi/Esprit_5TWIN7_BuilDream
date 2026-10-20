<?php

namespace App\Services\Maintenance;

use App\Contracts\AiClientInterface;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class DamagePhotoAnalysisService
{
    public function __construct(
        private readonly AiClientInterface $client,
        private readonly DamageSeverityClassifier $severityClassifier,
    ) {}

    /** @return array{damage_detected: bool, comments: string, severity: string, repair_hours: float, classification_source: string} */
    public function analyze(UploadedFile $photo): array
    {
        $result = $this->client->analyzeImage($photo->getRealPath(), (string) $photo->getMimeType());

        if (! array_key_exists('damage_detected', $result) || ! is_bool($result['damage_detected'])
            || ! isset($result['comments']) || ! is_string($result['comments'])) {
            throw new RuntimeException(__('Invalid AI response.'));
        }

        $comments = trim($result['comments']);
        // The image analysis already is an AI call. Classify locally here so
        // an unusually slow provider response cannot trigger a second call in
        // the same web request.
        $classification = $this->severityClassifier->classifyLocally($comments);

        return [
            'damage_detected' => $result['damage_detected'],
            'comments' => $comments,
            'severity' => $classification['severity'],
            'repair_hours' => $classification['repair_hours'],
            'classification_source' => $classification['source'],
        ];
    }
}
