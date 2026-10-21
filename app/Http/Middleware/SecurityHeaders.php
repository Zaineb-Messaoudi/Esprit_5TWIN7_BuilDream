<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Content Security Policy
        $response->headers->set('Content-Security-Policy', $this->buildCspPolicy($request));

        // HTTP Strict Transport Security (HSTS) - only in production over HTTPS
        if (app()->environment('production') && $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // X-Frame-Options (consistent with CSP frame-ancestors 'none')
        $response->headers->set('X-Frame-Options', 'DENY');

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
        $isDebug = config('app.debug');
        $isApi = $request->is('api/*');

        // For API routes, use a strict CSP (no resources are ever loaded)
        if ($isApi) {
            return "default-src 'none'; frame-ancestors 'none';";
        }

        $devOrigin = 'http://localhost:5173';
        $isLocalLoopback = app()->environment('local')
            && in_array($request->getHost(), ['localhost', '127.0.0.1'], true);

        $base = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://js.stripe.com https://cdn.tailwindcss.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tailwindcss.com",
            "font-src 'self' data: https://fonts.gstatic.com",
            "img-src 'self' data: https:",
            "connect-src 'self' https://api.stripe.com wss://localhost:8081 ws://localhost:8081",
            "frame-src 'self' https://js.stripe.com https://hooks.stripe.com",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
        ];

        // Local development servers may be opened through either loopback alias.
        // Keep this broader HTTP allowance out of every non-local environment.
        $base = array_map(function ($directive) use ($devOrigin, $isLocalLoopback, $isDebug) {
            if (str_starts_with($directive, 'form-action') && $isLocalLoopback) {
                return $directive.' http:';
            }

            if ($isDebug) {
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
            }

            return $directive;
        }, $base);

        return implode('; ', $base);
    }
}