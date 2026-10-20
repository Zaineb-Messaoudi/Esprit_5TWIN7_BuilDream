<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitCallbackWebhook
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  int  $maxAttempts
     * @param  int  $decayMinutes
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 10, int $decayMinutes = 5): Response
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

    /**
     * Resolve the rate limit key based on the callback secret.
     * Uses the callback secret as the primary identifier since it's unique per job.
     */
    protected function resolveKey(Request $request): string
    {
        // Try to get callback secret from various sources
        $secret = $request->header('X-Callback-Secret') 
            ?? $request->input('callback_secret')
            ?? $request->query('callback_secret');
        
        $ip = $request->ip();
        
        if ($secret) {
            // Hash the secret to avoid storing it in rate limiter keys
            $secretHash = substr(hash('sha256', $secret), 0, 16);
            return "callback_webhook:secret:{$secretHash}:ip:{$ip}";
        }

        // Fallback to IP-based limiting if no secret provided
        return "callback_webhook:ip:{$ip}";
    }

    protected function buildResponse(Request $request, string $key, int $maxAttempts, int $decayMinutes): Response
    {
        $retryAfter = RateLimiter::availableIn($key);

        return response()->json([
            'error' => 'Too many callback requests',
            'message' => "Callback rate limit exceeded. Try again in {$retryAfter} seconds.",
            'retry_after' => $retryAfter,
        ], 429)->header('Retry-After', $retryAfter);
    }

    protected function addHeaders(Response $response, string $key, int $maxAttempts): void
    {
        $remaining = RateLimiter::remaining($key, $maxAttempts);
        $reset = now()->addSeconds(RateLimiter::availableIn($key))->timestamp;

        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', max(0, $remaining));
        $response->headers->set('X-RateLimit-Reset', now()->addSeconds(RateLimiter::availableIn($key))->timestamp);
    }
}