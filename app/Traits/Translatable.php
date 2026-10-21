<?php

namespace App\Traits;

use App\Models\Translation;
use App\Models\TranslationKey;
use App\Services\AutomatedTranslationService;
use Illuminate\Support\Facades\App;

trait Translatable
{
    protected static function bootTranslatable(): void
    {
        static::created(function ($model) {
            $model->syncTranslations();
        });

        static::updated(function ($model) {
            $model->syncTranslations();
        });
    }

    public function getTranslatableFields(): array
    {
        return $this->translatable ?? [];
    }

    public function getTranslatedAttribute(string $attribute): ?string
    {
        $locale = App::getLocale();

        if ($locale === 'en' || empty($this->getOriginal($attribute))) {
            return $this->getOriginal($attribute);
        }

        $keyName = $this->getTranslatableKey($attribute);
        $translation = Translation::whereHas('key', fn ($q) => $q->where('key', $keyName))
            ->where('locale', $locale)
            ->first();

        return $translation?->value ?? $this->getOriginal($attribute);
    }

    public function syncTranslations(): void
    {
        $translator = app(AutomatedTranslationService::class);
        $targetLocales = $translator->getSupportedLocales();
        $sourceLocale = $translator->getSourceLocale();
        $translatableFields = $this->getTranslatableFields();

        if (empty($translatableFields)) {
            return;
        }

        foreach ($translatableFields as $field) {
            $value = $this->getOriginal($field) ?? $this->getAttribute($field);

            if (empty($value)) {
                continue;
            }

            $baseValue = $value;
            $keyName = $this->getTranslatableKey($field);

            $keyModel = TranslationKey::firstOrCreate(
                ['key' => $keyName],
                ['group' => strtolower(class_basename(static::class)), 'description' => "Auto-translation for {$this->getTable()}.{$field}"]
            );

            $sourceTranslation = Translation::firstOrCreate(
                ['translation_key_id' => $keyModel->id, 'locale' => $sourceLocale],
                ['value' => $baseValue]
            );

            if ($sourceTranslation->wasRecentlyCreated || $sourceTranslation->wasChanged()) {
                $sourceTranslation->save();
            }

            foreach ($targetLocales as $locale) {
                $alreadyHasTranslation = Translation::where('translation_key_id', $keyModel->id)
                    ->where('locale', $locale)
                    ->exists();

                if (! $alreadyHasTranslation || $sourceTranslation->wasRecentlyCreated || $sourceTranslation->wasChanged()) {
                    $translated = $translator->translate($baseValue, $locale);

                    Translation::updateOrCreate(
                        ['translation_key_id' => $keyModel->id, 'locale' => $locale],
                        ['value' => $translated]
                    );
                }
            }
        }
    }

    public function getTranslatableKey(string $field): string
    {
        return strtolower(class_basename(static::class)).'.'.$this->getKey().'.'.$field;
    }
}
