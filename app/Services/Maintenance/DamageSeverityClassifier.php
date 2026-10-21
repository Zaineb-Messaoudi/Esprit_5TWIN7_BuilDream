<?php

namespace App\Services\Maintenance;

use App\Contracts\AiClientInterface;
use Throwable;

class DamageSeverityClassifier
{
    public function __construct(private readonly AiClientInterface $client) {}

    public function classify(?string $comments): array
    {
        $comments = trim((string) $comments);
        if ($comments === '') {
            return ['severity' => 'minor', 'repair_hours' => 0, 'source' => 'fallback'];
        }

        try {
            $decoded = json_decode($this->client->classify('Classify as minor, moderate or severe and estimate repair_hours. Return strict JSON. Comment: '.$comments), true, 512, JSON_THROW_ON_ERROR);
            if (in_array($decoded['severity'] ?? null, ['minor', 'moderate', 'severe'], true) && is_numeric($decoded['repair_hours'] ?? null) && $decoded['repair_hours'] >= 0) {
                return ['severity' => $decoded['severity'], 'repair_hours' => (float) $decoded['repair_hours'], 'source' => 'ai'];
            }
        } catch (Throwable) {
        }

        return $this->classifyLocally($comments);
    }

    /**
     * Used after a vision request so a second remote call cannot make the same
     * HTTP request exceed PHP's execution-time limit.
     */
    public function classifyLocally(?string $comments): array
    {
        $text = mb_strtolower(trim((string) $comments));

        return match (true) {
            str_contains($text, 'incend') || str_contains($text, 'cassé') || str_contains($text, 'grave') => ['severity' => 'severe', 'repair_hours' => 16.0, 'source' => 'fallback'],
            str_contains($text, 'fissur') || str_contains($text, 'panne') || str_contains($text, 'modéré') => ['severity' => 'moderate', 'repair_hours' => 6.0, 'source' => 'fallback'],
            default => ['severity' => 'minor', 'repair_hours' => 2.0, 'source' => 'fallback'],
        };
    }
}
