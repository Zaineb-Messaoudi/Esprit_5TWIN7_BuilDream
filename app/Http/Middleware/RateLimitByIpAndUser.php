<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitByIpAndUser
{
    public function handle(Request $request, Closure $next, int $maxAttempts = 30, int $decayMinutes = 1): Response
    {
        $key = $this->resolveKey($request);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return $this->buildResponse($request, $key, $maxAttempts, $decayMinutes);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        $this->addHeaders($response, $key, $maxAttempts);

        return $response;
    }

    protected function resolveKey(Request $request): string
    {
        $ip = $request->ip();
        $userId = $request->user()?->id;

        // Combine IP + User for authenticated requests, IP only for guests
        if ($userId) {
            return "ai:{$request->route()->getName()}:user:{$userId}:ip:{$ip}";
        }

        return "ai:{$request->route()->getName()}:ip:{$ip}";
    }

    protected function buildResponse(Request $request, string $key, int $maxAttempts, int $decayMinutes): Response
    {
        $retryAfter = RateLimiter::availableIn($key);

        return response()->json([
            'error' => 'Too many requests',
            'message' => "Rate limit exceeded. Try again in {$retryAfter} seconds.",
            'retry_after' => $retryAfter,
        ], 429)->header('Retry-After', $retryAfter);
    }

    protected function addHeaders(Response $response, string $key, int $maxAttempts): void
    {
        $remaining = RateLimiter::remaining($key, $maxAttempts);
        $reset = now()->addSeconds(RateLimiter::availableIn($key))->timestamp;

        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', max(0, $remaining));
        $response->headers->set('X-RateLimit-Reset', $reset);
    }
}
