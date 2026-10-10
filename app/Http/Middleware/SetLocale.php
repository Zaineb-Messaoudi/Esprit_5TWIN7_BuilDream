<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * Set locale from user preference, session, or Accept-Language header.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        App::setLocale($locale);
        Session::put('locale', $locale);

        // Set RTL direction
        $i18n = app(\App\Services\I18nService::class);
        Session::put('rtl', $i18n->isRtl());

        // Set currency
        $user = $request->user();
        if ($user) {
            $i18n = app(\App\Services\I18nService::class);
            $currency = $i18n->getUserCurrency($user);
            Session::put('currency', $currency?->code);
            Session::put('currency_symbol', $currency?->symbol);
        }

        return $next($request);
    }

    /**
     * Determine the locale from various sources.
     */
    protected function resolveLocale(Request $request): string
    {
        // 1. Check session
        if (Session::has('locale')) {
            $locale = Session::get('locale');
            if ($this->isValidLocale($locale)) {
                return $locale;
            }
        }

        // 2. Check authenticated user's preference
        if ($request->user()) {
            $userLocale = $request->user()->locale;
            if ($userLocale && $this->isValidLocale($userLocale)) {
                return $userLocale;
            }
        }

        // 3. Check Accept-Language header
        $accepted = $request->getPreferredLanguage(['en', 'fr', 'ar', 'de', 'es', 'it', 'pt', 'tr']);
        if ($accepted && $this->isValidLocale($accepted)) {
            return $accepted;
        }

        // 4. Default to configured fallback
        return config('app.fallback_locale', 'en');
    }

    protected function isValidLocale(string $locale): bool
    {
        return in_array($locale, ['en', 'fr', 'ar', 'de', 'es', 'it', 'pt', 'tr']);
    }
}
