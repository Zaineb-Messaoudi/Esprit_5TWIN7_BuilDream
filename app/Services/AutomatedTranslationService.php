<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AutomatedTranslationService
{
    protected array $supportedSources = ['libretranslate', 'mock'];

    protected int $rateLimitMs;

    protected int $timeout;

    protected ?string $apiKey;

    protected string $endpoint;

    protected int $cacheTtl;

    public function __construct()
    {
        $config = config('translation');
        $this->endpoint = rtrim($config['libretranslate']['endpoint'], '/');
        $this->apiKey = $config['libretranslate']['api_key'];
        $this->timeout = $config['libretranslate']['timeout'];
        $this->rateLimitMs = $config['libretranslate']['rate_limit_ms'];
        $this->cacheTtl = $config['cache_ttl_minutes'] * 60;
    }

    public function getDriver(): string
    {
        return config('translation.driver', 'libretranslate');
    }

    public function getSupportedLocales(): array
    {
        return config('translation.target_locales', ['fr']);
    }

    public function getSourceLocale(): string
    {
        return config('translation.default_source_locale', 'en');
    }

    public function translate(string $text, string $targetLocale, ?string $sourceLocale = null): string
    {
        $sourceLocale = $sourceLocale ?? $this->getSourceLocale();

        if ($targetLocale === $sourceLocale) {
            return $text;
        }

        $cacheKey = "auto_translation_{$sourceLocale}_{$targetLocale}_".md5($text);

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $driver = $this->getDriver();

        if ($driver === 'mock' || app()->environment('testing')) {
            $translated = $this->mockTranslate($text, $targetLocale);
        } else {
            $translated = $this->libretranslateTranslate($text, $targetLocale, $sourceLocale);
        }

        if (! empty($translated) && $translated !== $text) {
            Cache::put($cacheKey, $translated, $this->cacheTtl);
        }

        return $translated;
    }

    protected function libretranslateTranslate(string $text, string $targetLocale, string $sourceLocale): string
    {
        try {
            usleep($this->rateLimitMs * 1000);

            $payload = [
                'q' => $text,
                'source' => $sourceLocale,
                'target' => $targetLocale,
                'format' => 'text',
            ];

            if ($this->apiKey) {
                $payload['api_key'] = $this->apiKey;
            }

            $response = Http::timeout($this->timeout)->post($this->endpoint.'/translate', $payload);

            if ($response->successful()) {
                $data = $response->json();
                $translated = $data['translatedText'] ?? null;

                if (! empty($translated)) {
                    return $translated;
                }
            }

            Log::warning('AutomatedTranslation: LibreTranslate request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::warning('AutomatedTranslation: LibreTranslate exception', [
                'error' => $e->getMessage(),
            ]);
        }

        return $this->mockTranslate($text, $targetLocale);
    }

    protected function mockTranslate(string $text, string $targetLocale): string
    {
        $mockTranslations = [
            'fr' => [
                'Save' => 'Enregistrer',
                'Cancel' => 'Annuler',
                'Delete' => 'Supprimer',
                'Edit' => 'Modifier',
                'View' => 'Voir',
                'Add' => 'Ajouter',
                'New' => 'Nouveau',
                'Create' => 'Créer',
                'Update' => 'Mettre à jour',
                'Download' => 'Télécharger',
                'Upload' => 'Télécharger',
                'Search' => 'Rechercher',
                'Filter' => 'Filtrer',
                'Sort' => 'Trier',
                'Export' => 'Exporter',
                'Import' => 'Importer',
                'Settings' => 'Paramètres',
                'Profile' => 'Profil',
                'Account' => 'Compte',
                'Logout' => 'Déconnexion',
                'Login' => 'Connexion',
                'Password' => 'Mot de passe',
                'Email' => 'Email',
                'Name' => 'Nom',
                'Description' => 'Description',
                'Status' => 'Statut',
                'Category' => 'Catégorie',
                'Brand' => 'Marque',
                'Model' => 'Modèle',
                'Price' => 'Prix',
                'Condition' => 'Condition',
                'Location' => 'Emplacement',
                'Available' => 'Disponible',
                'Pending' => 'En attente',
                'Approved' => 'Approuvé',
                'Rejected' => 'Rejeté',
                'Yes' => 'Oui',
                'No' => 'Non',
                'Actions' => 'Actions',
                'Submit' => 'Soumettre',
                'Save changes' => 'Enregistrer les modifications',
                'Are you sure?' => 'Êtes-vous sûr?',
            ],
        ];

        $key = trim($text);

        if (isset($mockTranslations[$targetLocale][$key])) {
            return $mockTranslations[$targetLocale][$key];
        }

        return $text;
    }
}
