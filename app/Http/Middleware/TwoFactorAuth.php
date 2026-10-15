<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Two-factor authentication middleware.
 *
 * Ensures the user has completed 2FA setup before accessing protected routes.
 */
class TwoFactorAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $next($request);
        }

        // Skip 2FA check for 2FA setup routes
        if ($this->isTwoFactorRoute($request)) {
            return $next($request);
        }

        $user = $request->user();

        // Check if 2FA is enabled but not confirmed
        if (! empty($user->two_factor_secret) && empty($user->two_factor_confirmed_at)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Two-factor authentication setup required.',
                    'action' => 'complete_2fa',
                    'redirect' => route('two-factor.setup'),
                ], 403);
            }

            return redirect()->route('two-factor.setup')
                ->with('warning', 'Please complete your two-factor authentication setup.');
        }

        // Check if 2FA is enabled but user hasn't verified in this session
        if ($this->isEnabled($user) && ! $this->isVerifiedInSession($request)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Two-factor authentication required.',
                    'action' => 'verify_2fa',
                    'redirect' => route('two-factor.verify'),
                ], 403);
            }

            return redirect()->route('two-factor.verify')
                ->with('warning', 'Please verify your two-factor authentication code.');
        }

        return $next($request);
    }

    /**
     * Check if the current route is a 2FA-related route.
     */
    protected function isTwoFactorRoute(Request $request): bool
    {
        $twoFactorRoutes = [
            'two-factor.setup',
            'two-factor.verify',
            'two-factor.disable',
            'two-factor.recovery-codes',
        ];

        foreach ($twoFactorRoutes as $route) {
            if ($request->routeIs($route) || $request->routeIs($route.'.*')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if 2FA is enabled for the user.
     */
    protected function isEnabled($user): bool
    {
        return ! empty($user->two_factor_secret) && ! empty($user->two_factor_confirmed_at);
    }

    /**
     * Check if 2FA has been verified in the current session.
     */
    protected function isVerifiedInSession(Request $request): bool
    {
        return $request->session()->has('2fa_verified');
    }
}
