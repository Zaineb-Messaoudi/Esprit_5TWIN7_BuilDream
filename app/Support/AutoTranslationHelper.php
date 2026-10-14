<?php

namespace App\Support;

use App\Services\AutomatedTranslationService;
use Illuminate\Support\Facades\Cache;

class AutoTranslationHelper
{
    protected AutomatedTranslationService $translator;

    public function __construct(AutomatedTranslationService $translator)
    {
        $this->translator = $translator;
    }

    public function translate(string $key, array $replace = [], ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $sourceLocale = $this->translator->getSourceLocale();

        if ($locale === $sourceLocale) {
            return $key;
        }

        $targetLocales = $this->translator->getSupportedLocales();
        if (!in_array($locale, $targetLocales)) {
            return $key;
        }

        $cacheKey = "auto_translation_runtime_{$sourceLocale}_{$locale}_" . md5($key);

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $translated = $this->translator->translate($key, $locale, $sourceLocale);
        Cache::put($cacheKey, $translated, 86400);

        return $translated;
    }
}