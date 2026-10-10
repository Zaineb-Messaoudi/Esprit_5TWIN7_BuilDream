<?php

namespace App\Http\Controllers\Api;

use App\Models\Currency;
use App\Models\LanguagePreference;
use App\Services\I18nService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Internationalization and currency API controller.
 */
class I18nController
{
    public function __construct(private readonly I18nService $service) {}

    /**
     * Get supported locales.
     */
    public function locales(Request $request): JsonResponse
    {
        return response()->json($this->service->getSupportedLocales());
    }

    /**
     * Get current locale info.
     */
    public function current(Request $request): JsonResponse
    {
        $info = $this->service->getCurrentLocaleInfo();
        $rtl = $this->service->getRtlClasses();
        
        return response()->json([
            'locale' => App::getLocale(),
            ...$info,
            'rtl' => $rtl,
        ]);
    }

    /**
     * Switch locale.
     */
    public function switch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'in:en,fr,ar,de,es,it,pt,tr'],
        ]);

        $success = $this->service->setLocale($validated['locale']);

        if (!$success) {
            return response()->json(['message' => 'Invalid locale.'], 400);
        }

        $info = $this->service->getCurrentLocaleInfo();
        $rtl = $this->service->getRtlClasses();

        return response()->json([
            'message' => 'Locale switched successfully.',
            'locale' => App::getLocale(),
            ...$info,
            'rtl' => $rtl,
        ]);
    }

    /**
     * Get supported currencies.
     */
    public function currencies(Request $request): JsonResponse
    {
        return response()->json($this->service->getSupportedCurrencies());
    }

    /**
     * Get current currency.
     */
    public function currentCurrency(Request $request): JsonResponse
    {
        $currency = $this->service->getUserCurrency($request->user());
        
        return response()->json([
            'currency' => $currency ? [
                'code' => $currency->code,
                'name' => $currency->name,
                'symbol' => $currency->symbol,
                'symbol_native' => $currency->symbol_native,
                'decimal_places' => $currency->decimal_places,
            ] : null,
        ]);
    }

    /**
     * Switch user's preferred currency.
     */
    public function switchCurrency(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'currency_code' => ['required', 'string', 'size:3', 'exists:currencies,code'],
        ]);

        $success = $this->service->setUserCurrency($request->user(), $validated['currency_code']);

        if (!$success) {
            return response()->json(['message' => 'Invalid currency.'], 400);
        }

        $currency = $this->service->getUserCurrency($request->user());

        return response()->json([
            'message' => 'Currency updated.',
            'currency' => $currency ? [
                'code' => $currency->code,
                'name' => $currency->name,
                'symbol' => $currency->symbol,
                'symbol_native' => $currency->symbol_native,
                'decimal_places' => $currency->decimal_places,
            ] : null,
        ]);
    }

    /**
     * Convert amount to user's currency.
     */
    public function convert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'from_currency' => ['nullable', 'string', 'size:3', 'exists:currencies,code'],
        );

        $user = $request->user();
        $fromCurrency = $validated['from_currency'] 
            ? Currency::where('code', $validated['from_currency'])->first()
            : $this->service->getUserCurrency($request->user());

        $converted = $this->service->convertToUserCurrency(
            $validated['amount'],
            $request->user(),
            $fromCurrency
        );

        $userCurrency = $this->service->getUserCurrency($request->user());

        return response()->json([
            'original_amount' => $validated['amount'],
            'original_currency' => $fromCurrency?->code,
            'converted_amount' => $converted,
            'target_currency' => $userCurrency?->code,
            'formatted' => $userCurrency ? $userCurrency->format($converted) : null,
        ]);
    }

    /**
     * Get exchange rate between two currencies.
     */
    public function exchangeRate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from' => ['required', 'string', 'size:3', 'exists:currencies,code'],
            'to' => ['required', 'string', 'size:3', 'exists:currencies,code'],
        ]);

        $from = Currency::where('code', $validated['from'])->firstOrFail();
        $to = Currency::where('code', $validated['to'])->firstOrFail();

        $rate = $this->service->getExchangeRate($from, $to);

        return response()->json([
            'from' => $from->code,
            'to' => $to->code,
            'rate' => $rate,
            'date' => now()->toDateString(),
        ]);
    }

    /**
     * Get user's locale preference.
     */
    public function userLocale(Request $request): JsonResponse
    {
        $user = $request->user();
        $locale = $this->service->getUserLocale($user);
        $info = $this->service->getSupportedLocales()[$locale] ?? $this->service->getSupportedLocales()['en'];

        return response()->json([
            'locale' => $locale,
            ...$info,
        ]);
    }

    /**
     * Update user's locale preference.
     */
    public function updateUserLocale(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'in:en,fr,ar,de,es,it,pt,tr'],
        ]);

        $success = $this->service->setUserLocale($request->user(), $validated['locale']);

        if (!$success) {
            return response()->json(['message' => 'Invalid locale.'], 400);
        }

        return response()->json([
            'message' => 'Locale updated.',
            'locale' => $validated['locale'],
        ]);
    }

    /**
     * Update user's currency preference.
     */
    public function updateUserCurrency(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'currency_code' => ['required', 'string', 'size:3', 'exists:currencies,code'],
        ]);

        $success = $this->service->setUserCurrency($request->user(), $validated['currency_code']);

        if (!$success) {
            return response()->json(['message' => 'Invalid currency.'], 400);
        }

        return response()->json(['message' => 'Currency preference updated.']);
    }

    /**
     * Get RTL classes for frontend.
     */
    public function rtl(Request $request): JsonResponse
    {
        return response()->json($this->service->getRtlClasses());
    }

    /**
     * Get locale switcher data.
     */
    public function switcherData(Request $request): JsonResponse
    {
        return response()->json($this->service->getLocaleSwitcherData());
    }
}