<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageLog extends Model
{
    protected $fillable = [
        'endpoint',
        'provider',
        'model',
        'user_id',
        'request_id',
        'request_payload',
        'response_payload',
        'latency_ms',
        'tokens_input',
        'tokens_output',
        'tokens_total',
        'cost_usd',
        'gpu_time_ms',
        'vram_used_mb',
        'success',
        'error_message',
        'prompt_hash',
        'quality_rating',
        'quality_feedback',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'latency_ms' => 'integer',
        'tokens_input' => 'integer',
        'tokens_output' => 'integer',
        'tokens_total' => 'integer',
        'cost_usd' => 'decimal:6',
        'gpu_time_ms' => 'integer',
        'vram_used_mb' => 'integer',
        'success' => 'boolean',
        'quality_rating' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(
        string $endpoint,
        string $provider,
        string $model,
        array $data
    ): self {
        return self::create([
            'endpoint' => $endpoint,
            'provider' => $provider,
            'model' => $model,
            'user_id' => $data['user_id'] ?? null,
            'request_id' => $data['request_id'] ?? null,
            'request_payload' => $data['request_payload'] ?? null,
            'response_payload' => $data['response_payload'] ?? null,
            'latency_ms' => $data['latency_ms'] ?? 0,
            'tokens_input' => $data['tokens_input'] ?? 0,
            'tokens_output' => $data['tokens_output'] ?? 0,
            'tokens_total' => $data['tokens_total'] ?? 0,
            'cost_usd' => $data['cost_usd'] ?? 0,
            'gpu_time_ms' => $data['gpu_time_ms'] ?? null,
            'vram_used_mb' => $data['vram_used_mb'] ?? null,
            'success' => $data['success'] ?? true,
            'error_message' => $data['error_message'] ?? null,
            'prompt_hash' => $data['prompt_hash'] ?? null,
            'quality_rating' => $data['quality_rating'] ?? 0,
            'quality_feedback' => $data['quality_feedback'] ?? null,
        ]);
    }

    public static function getStats(?string $endpoint = null, string $period = '24h'): array
    {
        $query = self::query();

        if ($endpoint) {
            $query->where('endpoint', $endpoint);
        }

        $since = match ($period) {
            '1h' => now()->subHour(),
            '24h' => now()->subDay(),
            '7d' => now()->subWeek(),
            '30d' => now()->subMonth(),
            default => now()->subDay(),
        };

        $query->where('created_at', '>=', $since);

        $logs = $query->get();

        $total = $logs->count();
        $successful = $logs->where('success', true)->count();
        $withFeedback = $logs->where('quality_rating', '!=', 0)->count();
        $positive = $logs->where('quality_rating', 1)->count();
        $negative = $logs->where('quality_rating', -1)->count();

        return [
            'total_requests' => $total,
            'success_rate' => $total > 0 ? round($successful / $total * 100, 2) : 0,
            'avg_latency_ms' => $logs->avg('latency_ms') ?? 0,
            'total_tokens' => $logs->sum('tokens_total'),
            'total_cost_usd' => round($logs->sum('cost_usd'), 6),
            'avg_gpu_time_ms' => $logs->avg('gpu_time_ms') ?? 0,
            'avg_vram_mb' => $logs->avg('vram_used_mb') ?? 0,
            'error_count' => $logs->where('success', false)->count(),
            'feedback' => [
                'total_feedback' => $withFeedback,
                'feedback_rate' => $total > 0 ? round($withFeedback / $total * 100, 2) : 0,
                'positive' => $positive,
                'negative' => $negative,
                'satisfaction_rate' => $withFeedback > 0 ? round($positive / $withFeedback * 100, 2) : 0,
            ],
        ];
    }

    /**
     * Record quality feedback (thumbs up/down) for a request.
     */
    public static function recordFeedback(string $requestId, int $rating, ?string $feedback = null): bool
    {
        if (! in_array($rating, [1, -1])) {
            return false;
        }

        return self::where('request_id', $requestId)
            ->update([
                'quality_rating' => $rating,
                'quality_feedback' => $feedback,
            ]) > 0;
    }
}
