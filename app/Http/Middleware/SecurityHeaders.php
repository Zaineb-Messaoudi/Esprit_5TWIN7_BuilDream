<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security headers middleware for CSP, HSTS, X-Frame-Options, etc.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Content Security Policy
        $csp = $this->buildCspPolicy($request);
        $response->headers->set('Content-Security-Policy', $csp);

        // HTTP Strict Transport Security (HSTS)
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // X-Frame-Options
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // X-Content-Type-Options
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // X-XSS-Protection (legacy but still useful)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=()');

        // Cross-Origin policies
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');

        return $response;
    }

    private function buildCspPolicy(Request $request): string
    {
        $isDev = config('app.debug');
        $isApi = $request->is('api/*');

        // For API routes, use a more permissive CSP
        if ($isApi) {
            return "default-src 'none'; frame-ancestors 'none';";
        }

        $devOrigin = 'http://localhost:5173';
        $devWs = 'ws://localhost:5173';

        $base = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com https://cdn.tailwindcss.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://unpkg.com https://cdn.tailwindcss.com",
            "font-src 'self' data: https://fonts.gstatic.com https://cdn.jsdelivr.net https://unpkg.com",
            "img-src 'self' data: blob: https: https://*.tile.openstreetmap.org",
            "connect-src 'self' wss://*.pusher.com",
            "frame-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ];

        if (config('app.debug')) {
            // Merge dev origins into existing directives instead of duplicating
            $base = array_map(function ($directive) use ($devOrigin) {
                if (str_starts_with($directive, 'script-src')) {
                    return $directive.' '.$devOrigin;
                }
                if (str_starts_with($directive, 'style-src')) {
                    return $directive.' '.$devOrigin;
                }
                if (str_starts_with($directive, 'font-src')) {
                    return $directive.' '.$devOrigin;
                }
                if (str_starts_with($directive, 'connect-src')) {
                    return $directive.' '.$devOrigin.' ws: wss:';
                }

                return $directive;
            }, $base);
        }

        return implode('; ', $base);
    }
}
