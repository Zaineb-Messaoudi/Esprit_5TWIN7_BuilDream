<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Add rate limit headers to responses.
 */
class RateLimitHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Add rate limit headers if available
        if ($request->hasHeader('X-RateLimit-Limit')) {
            return $response;
        }

        // For authenticated users, add user-based rate limit info
        if ($request->user()) {
            $key = 'rate_limit:'.$request->user()->id;
            $limit = 60; // requests per minute
            $remaining = \Illuminate\Support\Facades\Cache::get($key, 0);
            $remaining = max(0, $limit - $remaining);

            $response->headers->set('X-RateLimit-Limit', $limit);
            $response->headers->set('X-RateLimit-Remaining', $remaining);
            $response->headers->set('X-RateLimit-Reset', now()->addMinute()->timestamp);
        }

        return $response;
    }
}
