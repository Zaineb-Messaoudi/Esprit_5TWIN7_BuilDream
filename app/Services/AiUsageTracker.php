<?php

namespace App\Services;

use App\Models\AiUsageLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiUsageTracker
{
    private string $requestId;

    public function __construct()
    {
        $this->requestId = Str::uuid()->toString();
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }

    public function track(
        string $endpoint,
        callable $callback,
        array $context = []
    ): array {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        $success = false;
        $errorMessage = null;
        $response = null;
        $tokensInput = 0;
        $tokensOutput = 0;
        $gpuTimeMs = null;
        $vramUsedMb = null;

        try {
            $result = $callback($this);
            $success = true;
            $response = $result;

            // Extract token usage from response if available
            if (is_array($result)) {
                $tokensInput = $result['tokens_input'] ?? 0;
                $tokensOutput = $result['tokens_output'] ?? 0;
                $gpuTimeMs = $result['gpu_time_ms'] ?? null;
                $vramUsedMb = $result['vram_used_mb'] ?? null;
            }
        } catch (\Throwable $e) {
            $success = false;
            $errorMessage = $e->getMessage();
            throw $e;
        } finally {
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);
            $memoryUsed = memory_get_usage(true) - $startMemory;

            $this->logUsage($endpoint, $success, $errorMessage, $latencyMs, [
                'tokens_input' => $tokensInput,
                'tokens_output' => $tokensOutput,
                'gpu_time_ms' => $gpuTimeMs,
                'vram_used_mb' => $vramUsedMb,
                'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2),
            ]);
        }

        return [
            'success' => $success,
            'response' => $response,
            'error' => $errorMessage,
            'latency_ms' => (int) round((microtime(true) - $startTime) * 1000),
            'request_id' => $this->requestId,
        ];
    }

    public function trackAsync(
        string $endpoint,
        \App\Jobs\ProcessAiRequest $job,
        array $payload
    ): string {
        $jobId = $job->getJobId() ?? Str::uuid()->toString();
        $this->requestId = $jobId;

        // Log job dispatch
        AiUsageLog::log($endpoint, config('ai.provider'), config('ai.model'), [
            'user_id' => auth()->id(),
            'request_id' => $this->requestId,
            'request_payload' => $payload,
            'latency_ms' => 0, // Will be updated on completion
            'success' => true,
            'prompt_hash' => $payload['prompt_hash'] ?? null,
        ]);

        return $jobId;
    }

    public function completeAsync(
        string $endpoint,
        string $requestId,
        array $result,
        bool $success = true,
        ?string $errorMessage = null
    ): void {
        // Update the log entry with completion data
        AiUsageLog::where('request_id', $requestId)
            ->where('endpoint', $endpoint)
            ->update([
                'response_payload' => $result,
                'latency_ms' => $result['latency_ms'] ?? 0,
                'tokens_input' => $result['tokens_input'] ?? 0,
                'tokens_output' => $result['tokens_output'] ?? 0,
                'tokens_total' => ($result['tokens_input'] ?? 0) + ($result['tokens_output'] ?? 0),
                'cost_usd' => $result['cost_usd'] ?? 0,
                'gpu_time_ms' => $result['gpu_time_ms'] ?? null,
                'vram_used_mb' => $result['vram_used_mb'] ?? null,
                'success' => $success,
                'error_message' => $errorMessage,
            ]);
    }

    private function logUsage(
        string $endpoint,
        bool $success,
        ?string $errorMessage,
        int $latencyMs,
        array $metrics = []
    ): void {
        $userId = auth()->id();

        AiUsageLog::log($endpoint, config('ai.provider'), config('ai.model'), [
            'user_id' => $userId,
            'request_id' => $this->requestId,
            'latency_ms' => $latencyMs,
            'tokens_input' => $metrics['tokens_input'] ?? 0,
            'tokens_output' => $metrics['tokens_output'] ?? 0,
            'tokens_total' => ($metrics['tokens_input'] ?? 0) + ($metrics['tokens_output'] ?? 0),
            'cost_usd' => $this->estimateCost($endpoint, $metrics),
            'gpu_time_ms' => $metrics['gpu_time_ms'] ?? null,
            'vram_used_mb' => $metrics['vram_used_mb'] ?? null,
            'success' => $success,
            'error_message' => $errorMessage,
        ]);

        // Also log to ai channel for debugging
        Log::channel('ai')->info('AI request completed', [
            'endpoint' => $endpoint,
            'request_id' => $this->requestId,
            'success' => $success,
            'latency_ms' => $latencyMs,
            'tokens' => $metrics['tokens_input'] ?? 0 + $metrics['tokens_output'] ?? 0,
            'cost_usd' => $this->estimateCost($endpoint, $metrics),
            'gpu_time_ms' => $metrics['gpu_time_ms'],
            'vram_mb' => $metrics['vram_used_mb'],
        ]);
    }

    private function estimateCost(string $endpoint, array $metrics): float
    {
        // Cost estimates for paid providers (per 1K tokens)
        $costs = [
            'openai' => [
                'gpt-4o' => ['input' => 0.005, 'output' => 0.015],
                'gpt-4o-mini' => ['input' => 0.00015, 'output' => 0.0006],
                'gpt-3.5-turbo' => ['input' => 0.0005, 'output' => 0.0015],
            ],
            'anthropic' => [
                'claude-3-opus' => ['input' => 0.015, 'output' => 0.075],
                'claude-3-sonnet' => ['input' => 0.003, 'output' => 0.015],
                'claude-3-haiku' => ['input' => 0.00025, 'output' => 0.00125],
            ],
        ];

        $provider = config('ai.provider');
        $model = config('ai.model');

        if ($provider === 'ollama') {
            return 0.0; // Free local
        }

        if (! isset($costs[$provider][$model])) {
            return 0.0;
        }

        $inputTokens = ($metrics['tokens_input'] ?? 0) / 1000;
        $outputTokens = ($metrics['tokens_output'] ?? 0) / 1000;

        return round(
            ($inputTokens * $costs[$provider][$model]['input']) +
            ($outputTokens * $costs[$provider][$model]['output']),
            6
        );
    }

    public static function getDashboardStats(): array
    {
        return [
            '24h' => AiUsageLog::getStats(null, '24h'),
            '7d' => AiUsageLog::getStats(null, '7d'),
            '30d' => AiUsageLog::getStats(null, '30d'),
            'by_endpoint' => self::getByEndpointStats(),
            'by_provider' => self::getByProviderStats(),
        ];
    }

    private static function getByEndpointStats(): array
    {
        $endpoints = \App\Models\AiUsageLog::select('endpoint')
            ->distinct()
            ->pluck('endpoint');

        $stats = [];
        foreach ($endpoints as $endpoint) {
            $stats[$endpoint] = self::getStats($endpoint, '24h');
        }

        return $stats;
    }

    private static function getByProviderStats(): array
    {
        $providers = \App\Models\AiUsageLog::select('provider')
            ->distinct()
            ->pluck('provider');

        $stats = [];
        foreach ($providers as $provider) {
            $providerLogs = \App\Models\AiUsageLog::where('provider', $provider)
                ->where('created_at', '>=', now()->subDay())
                ->get();

            $stats[$provider] = [
                'requests' => $providerLogs->count(),
                'success_rate' => $providerLogs->count() > 0
                    ? round($providerLogs->where('success', true)->count() / $providerLogs->count() * 100, 2)
                    : 0,
                'avg_latency_ms' => $providerLogs->avg('latency_ms') ?? 0,
                'total_tokens' => $providerLogs->sum('tokens_total'),
                'total_cost_usd' => round($providerLogs->sum('cost_usd'), 6),
            ];
        }

        return $stats;
    }
}
