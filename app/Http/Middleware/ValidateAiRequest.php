<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ValidateAiRequest
{
    protected array $endpointRules = [
        'natural-language-search' => [
            'q' => 'required|string|max:500',
        ],
        'generate-description' => [
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'energy_profile' => 'nullable|array',
        ],
        'suggest-price' => [
            'category' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'power_watts' => 'nullable|integer|min:0|max:100000',
            'capacity_wh' => 'nullable|integer|min:0|max:1000000',
        ],
        'auto-categorize' => [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
        ],
        'chatbot' => [
            'message' => 'required|string|max:2000',
            'context_equipment_ids' => 'nullable|array',
            'context_equipment_ids.*' => 'integer|exists:equipment,id',
        ],
        'semantic-search' => [
            'q' => 'required|string|max:500',
        ],
        'validate-image' => [
            'image' => 'required|image|max:5120',
            'equipment_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
        ],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $endpoint = $request->route()->getName();
        $endpoint = str_replace('ai.', '', $endpoint);

        // Validate request size
        if ($request->getContentLength() > 10 * 1024 * 1024) { // 10MB
            return response()->json([
                'error' => 'Request too large',
                'message' => 'Maximum request size is 10MB',
            ], 413);
        }

        // Validate content type for JSON endpoints
        if (!$request->is('api/ai/validate-image') && !$request->isJson()) {
            return response()->json([
                'error' => 'Invalid content type',
                'message' => 'Expected application/json',
            ], 415);
        }

        // Apply sanitization to all string inputs
        $this->sanitizeRequest($request);

        // Validate against endpoint-specific rules
        if (isset($this->endpointRules[$endpoint])) {
            $validator = \Illuminate\Support\Facades\Validator::make(
                $request->all(),
                $this->endpointRules[$endpoint]
            );

            if ($validator->fails()) {
                Log::warning('AI request validation failed', [
                    'endpoint' => $endpoint,
                    'errors' => $validator->errors()->toArray(),
                    'ip' => $request->ip(),
                    'user_id' => $request->user()?->id,
                ]);

                return response()->json([
                    'error' => 'Validation failed',
                    'details' => $validator->errors(),
                ], 422);
            }
        }

        return $next($request);
    }

    protected function sanitizeRequest(Request $request): void
    {
        $input = $request->all();

        $sanitized = $this->sanitizeArray($input);

        $request->replace($sanitized);
    }

    protected function sanitizeArray(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = $this->sanitizeString($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    protected function sanitizeString(string $input): string
    {
        $input = trim($input);
        $input = Str::limit($input, 5000, '');

        // Remove potential injection patterns
        $patterns = [
            '/ignore\s+(previous|above|system)\s+(instructions?|prompts?)/i',
            '/forget\s+(everything|all|previous)/i',
            '/you\s+are\s+now\s+/i',
            '/system\s*:\s*/i',
            '/assistant\s*:\s*/i',
            '/```/i',
            '/<\|.*?\|>/',
        ];

        foreach ($patterns as $pattern) {
            $input = preg_replace($pattern, '[filtered]', $input);
        }

        // Limit excessive whitespace
        $input = preg_replace('/\s{3,}/', '  ', $input);

        return $input;
    }
}