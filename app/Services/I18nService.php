<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\CurrencyExchangeRate;
use App\Models\LanguagePreference;
use App\Models\Translation;
use App\Models\TranslationKey;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class I18nService
{
    /**
     * Get supported locales with metadata.
     */
    public function getSupportedLocales(): array
    {
        return [
            'en' => [
                'name' => 'English',
                'native' => 'English',
                'rtl' => false,
                'flag' => '🇺🇸',
                'currency' => 'USD',
                'date_format' => 'M d, Y',
                'number_format' => ['decimal' => '.', 'thousands' => ','],
            ],
            'fr' => [
                'name' => 'French',
                'native' => 'Français',
                'rtl' => false,
                'flag' => '🇫🇷',
                'currency' => 'EUR',
                'date_format' => 'd/m/Y',
                'number_format' => ['decimal' => ',', 'thousands' => ' '],
            ],
            'ar' => [
                'name' => 'Arabic',
                'native' => 'العربية',
                'rtl' => true,
                'flag' => '🇸🇦',
                'currency' => 'SAR',
                'date_format' => 'd/m/Y',
                'number_format' => ['decimal' => '.', 'thousands' => ','],
            ],
            'de' => [
                'name' => 'German',
                'native' => 'Deutsch',
                'rtl' => false,
                'flag' => '🇩🇪',
                'currency' => 'EUR',
                'date_format' => 'd.m.Y',
                'number_format' => ['decimal' => ',', 'thousands' => '.'],
            ],
            'es' => [
                'name' => 'Spanish',
                'native' => 'Español',
                'rtl' => false,
                'flag' => '🇪🇸',
                'currency' => 'EUR',
                'date_format' => 'd/m/Y',
                'number_format' => ['decimal' => ',', 'thousands' => '.'],
            ],
            'it' => [
                'name' => 'Italian',
                'native' => 'Italiano',
                'rtl' => false,
                'flag' => '🇮🇹',
                'currency' => 'EUR',
                'date_format' => 'd/m/Y',
                'number_format' => ['decimal' => ',', 'thousands' => '.'],
            ],
            'pt' => [
                'name' => 'Portuguese',
                'native' => 'Português',
                'rtl' => false,
                'flag' => '🇵🇹',
                'currency' => 'EUR',
                'date_format' => 'd/m/Y',
                'number_format' => ['decimal' => ',', 'thousands' => '.'],
            ],
            'tr' => [
                'name' => 'Turkish',
                'native' => 'Türkçe',
                'rtl' => false,
                'flag' => '🇹🇷',
                'currency' => 'TRY',
                'date_format' => 'dd.MM.yyyy',
                'number_format' => ['decimal' => ',', 'thousands' => '.'],
            ],
        ];
    }

    /**
     * Set the application locale.
     */
    public function setLocale(string $locale): bool
    {
        $locales = $this->getSupportedLocales();

        if (! isset($locales[$locale])) {
            return false;
        }

        App::setLocale($locale);
        Session::put('locale', $locale);

        // Set RTL direction
        $localeInfo = $locales[$locale];
        Session::put('rtl', $localeInfo['rtl']);

        return true;
    }

    /**
     * Get current locale info.
     */
    public function getCurrentLocaleInfo(): array
    {
        $locale = App::getLocale();
        $locales = $this->getSupportedLocales();

        return $locales[$locale] ?? $locales['en'];
    }

    /**
     * Check if current locale is RTL.
     */
    public function isRtl(): bool
    {
        return $this->getCurrentLocaleInfo()['rtl'] ?? false;
    }

    /**
     * Get user's preferred locale.
     */
    public function getUserLocale(User $user): string
    {
        if ($user->locale && isset($this->getSupportedLocales()[$user->locale])) {
            return $user->locale;
        }

        $pref = LanguagePreference::where('user_id', $user->id)->first();
        if ($pref && isset($this->getSupportedLocales()[$pref->locale])) {
            return $pref->locale;
        }

        return App::getLocale();
    }

    /**
     * Set user's locale preference.
     */
    public function setUserLocale(User $user, string $locale): bool
    {
        $locales = $this->getSupportedLocales();

        if (! isset($locales[$locale])) {
            return false;
        }

        $user->update(['locale' => $locale]);

        LanguagePreference::updateOrCreate(
            ['user_id' => $user->id],
            [
                'locale' => $locale,
                'is_rtl' => $this->getSupportedLocales()[$locale]['rtl'] ?? false,
            ]
        );

        return true;
    }

    /**
     * Get user's currency preference.
     */
    public function getUserCurrency(User $user): ?Currency
    {
        if ($user->currency_code) {
            return Currency::where('code', $user->currency_code)->where('is_active', true)->first();
        }

        $pref = LanguagePreference::where('user_id', $user->id)->first();
        if ($pref && $pref->currency_code) {
            return Currency::where('code', $pref->currency_code)->where('is_active', true)->first();
        }

        // Return organization default or base currency
        if ($user->currentOrganization) {
            return Currency::where('code', $user->currentOrganization->default_currency_code)
                ->where('is_active', true)
                ->first();
        }

        return Currency::getBase();
    }

    /**
     * Set user's currency preference.
     */
    public function setUserCurrency(User $user, string $currencyCode): bool
    {
        $currency = Currency::where('code', $currencyCode)->where('is_active', true)->first();

        if (! $currency) {
            return false;
        }

        $user->update(['currency_code' => $currencyCode]);

        LanguagePreference::updateOrCreate(
            ['user_id' => $user->id],
            ['currency_code' => $currencyCode]
        );

        return true;
    }

    /**
     * Convert amount to user's preferred currency.
     */
    public function convertToUserCurrency(float $amount, User $user, ?Currency $fromCurrency = null): float
    {
        $userCurrency = $this->getUserCurrency($user);

        if (! $userCurrency || ! $fromCurrency) {
            return $amount;
        }

        if ($fromCurrency->id === $userCurrency->id) {
            return $amount;
        }

        $rate = CurrencyExchangeRate::getRate($fromCurrency, $userCurrency);

        return round($amount * $rate, $userCurrency->decimal_places);
    }

    /**
     * Format amount in user's preferred currency.
     */
    public function formatForUser(float $amount, User $user, ?Currency $fromCurrency = null): string
    {
        $converted = $this->convertToUserCurrency($amount, $user, $fromCurrency);
        $userCurrency = $this->getUserCurrency($user);

        return $userCurrency ? $userCurrency->format($converted) : number_format($amount, 2);
    }

    /**
     * Get translation for a key.
     */
    public function translate(string $key, array $replace = [], ?string $locale = null): string
    {
        $locale = $locale ?? App::getLocale();

        $translation = Translation::whereHas('key', fn ($q) => $q->where('key', $key))
            ->where('locale', $locale)
            ->first();

        if (! $translation) {
            // Fallback to English
            if ($locale !== 'en') {
                $translation = Translation::whereHas('key', fn ($q) => $q->where('key', $key))
                    ->where('locale', 'en')
                    ->first();
            }
        }

        $text = $translation?->value ?? $key;

        // Replace placeholders
        foreach ($replace as $key => $value) {
            $text = str_replace(':'.$key, $value, $text);
        }

        return $text;
    }

    /**
     * Get all translations for a locale (for frontend).
     */
    public function getTranslationsForLocale(string $locale): array
    {
        return Cache::remember("translations.{$locale}", 3600, function () use ($locale) {
            return Translation::where('locale', $locale)
                ->whereHas('key')
                ->get()
                ->mapWithKeys(fn ($t) => [$t->key->key => $t->value])
                ->toArray();
        });
    }

    /**
     * Get or create a translation key.
     */
    public function getOrCreateKey(string $key, ?string $group = null, ?string $description = null): TranslationKey
    {
        return TranslationKey::firstOrCreate(
            ['key' => $key],
            ['group' => $group, 'description' => $description]
        );
    }

    /**
     * Set translation for a key in a locale.
     */
    public function setTranslation(string $key, string $locale, string $value, ?string $group = null): Translation
    {
        $keyModel = $this->getOrCreateKey($key, $group);

        return Translation::updateOrCreate(
            [
                'translation_key_id' => $keyModel->id,
                'locale' => $locale,
            ],
            ['value' => $value]
        );
    }

    /**
     * Get supported currencies.
     */
    public function getSupportedCurrencies(): array
    {
        return Currency::active()->get()->map(fn ($c) => [
            'code' => $c->code,
            'name' => $c->name,
            'symbol' => $c->symbol,
            'symbol_native' => $c->symbol_native,
            'decimal_places' => $c->decimal_places,
            'is_base' => $c->is_base,
        ])->toArray();
    }

    /**
     * Get exchange rate between two currencies.
     */
    public function getExchangeRate(Currency $from, Currency $to): float
    {
        return CurrencyExchangeRate::getRate($from, $to);
    }

    /**
     * Format number for locale.
     */
    public function formatNumber(float $number, ?string $locale = null): string
    {
        $locale = $locale ?? App::getLocale();
        $info = $this->getSupportedLocales()[$locale] ?? $this->getSupportedLocales()['en'];

        $decimal = $info['number_format']['decimal'];
        $thousands = $info['number_format']['thousands'];

        return number_format($number, 2, $decimal, $thousands);
    }

    /**
     * Format date for locale.
     */
    public function formatDate(\DateTimeInterface $date, ?string $locale = null, ?string $format = null): string
    {
        $locale = $locale ?? App::getLocale();
        $info = $this->getSupportedLocales()[$locale] ?? $this->getSupportedLocales()['en'];

        $format = $format ?? $info['date_format'];

        return \Carbon\Carbon::parse($date)->locale($locale)->translatedFormat($format);
    }

    /**
     * Get RTL CSS classes.
     */
    public function getRtlClasses(): array
    {
        if ($this->isRtl()) {
            return [
                'dir' => 'rtl',
                'html_class' => 'rtl',
                'body_class' => 'rtl',
            ];
        }

        return [
            'dir' => 'ltr',
            'html_class' => 'ltr',
            'body_class' => 'ltr',
        ];
    }

    /**
     * Get locale switcher data for frontend.
     */
    public function getLocaleSwitcherData(): array
    {
        $currentLocale = App::getLocale();
        $locales = $this->getSupportedLocales();

        return array_map(function ($info, $code) use ($currentLocale) {
            return [
                'code' => $code,
                'name' => $info['name'],
                'native' => $info['native'],
                'flag' => $info['flag'],
                'rtl' => $info['rtl'],
                'current' => $code === $currentLocale,
            ];
        }, $locales, array_keys($locales));
    }
}
