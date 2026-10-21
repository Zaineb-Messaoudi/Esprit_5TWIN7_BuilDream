<?php

namespace App\Services;

use Illuminate\Support\Str;

class ReservationAssistantService
{
    /**
     * @return array<string, array{label: string, terms: list<string>}>
     */
    public function serviceTypes(): array
    {
        return [
            'solar' => [
                'label' => __('Solar panels'),
                'terms' => ['chauffe eau solaire', 'panneaux solaires', 'panneau solaire', 'photovoltaique', 'solaire'],
            ],
            'waterproofing' => [
                'label' => __('Waterproofing'),
                'terms' => ['etancheite', 'infiltrations', 'infiltration', 'humidite', 'toiture qui fuit', 'terrasse qui fuit'],
            ],
            'air_conditioning' => [
                'label' => __('Air conditioning'),
                'terms' => ['climatisation', 'climatiseur', 'clim', 'split', 'chauffage'],
            ],
            'plastering' => [
                'label' => __('Plastering'),
                'terms' => ['faux plafond', 'platrier', 'platre', 'placo', 'staff'],
            ],
            'masonry' => [
                'label' => __('Masonry'),
                'terms' => ['maconnerie', 'macon', 'cloison', 'beton', 'dalle', 'parpaing'],
            ],
            'gardening' => [
                'label' => __('Gardening'),
                'terms' => ['jardinage', 'jardinier', 'jardin', 'pelouse', 'taille de haies'],
            ],
            'renovation' => [
                'label' => __('General renovation'),
                'terms' => ['renovation generale', 'travaux de renovation', 'renovation', 'renover'],
            ],
            'plumbing' => [
                'label' => __('Plumbing'),
                'terms' => ['plomberie', 'plombier', 'canalisation', 'robinet', 'fuite', 'chauffe eau', 'siphon', 'wc', 'sanitaire', 'evier', 'douche'],
            ],
            'electricity' => [
                'label' => __('Electrical work'),
                'terms' => ['reparation electrique', 'electricite', 'electricien', 'electrique', 'tableau electrique', 'prise electrique', 'prises', 'prise', 'cablage', 'interrupteur', 'disjoncteur', 'luminaire', 'eclairage'],
            ],
            'painting' => [
                'label' => __('Painting'),
                'terms' => ['peinture', 'peindre', 'repeindre', 'peintre', 'vernis', 'enduit', 'ravalement'],
            ],
            'tiling' => [
                'label' => __('Tiling'),
                'terms' => ['pose de carrelage', 'carrelage', 'carreleur', 'carreler', 'faience', 'marbre', 'sol'],
            ],
            'carpentry' => [
                'label' => __('Carpentry'),
                'terms' => ['menuiserie', 'menuisier', 'fenetre', 'porte', 'placard', 'serrure', 'parquet', 'volet', 'bois'],
            ],
        ];
    }

    /**
     * @return array{
     *     status: string,
     *     message: string,
     *     fields: array{service_type: ?string, custom_description: ?string, surface_m2: ?float, urgency: ?string, budget: ?float, address: ?string, city: ?string},
     *     missing_fields: list<string>
     * }
     */
    public function analyze(string $description): array
    {
        $normalized = Str::lower(Str::ascii($description));
        $types = $this->serviceTypes();
        $serviceType = null;

        foreach ($types as $key => $type) {
            foreach ($type['terms'] as $term) {
                if (preg_match('/\b'.preg_quote($term, '/').'\b/u', $normalized) === 1) {
                    $serviceType = $key;
                    break 2;
                }
            }
        }

        $isWorkRequest = preg_match('/\b(travaux|chantier|renov|installer|installation|reparer|reparation|amenagement|construction)\b/u', $normalized) === 1;
        $isCustomQuote = $serviceType === null && $isWorkRequest;

        $fields = [
            'service_type' => $serviceType ?? ($isCustomQuote ? 'custom_quote' : null),
            'custom_description' => $isCustomQuote ? trim($description) : null,
            'surface_m2' => $this->extractSurface($normalized),
            'urgency' => $this->extractUrgency($normalized),
            'budget' => $this->extractBudget($normalized),
            'address' => $this->extractAddress($description),
            'city' => $this->extractCity($this->extractAddress($description)) ?? $this->detectKnownCity($normalized),
        ];

        $missingFields = [];
        foreach (['service_type', 'urgency', 'budget', 'address', 'city'] as $requiredField) {
            if ($fields[$requiredField] === null) {
                $missingFields[] = $requiredField;
            }
        }

        if ($fields['service_type'] === 'custom_quote' && blank($fields['custom_description'])) {
            $missingFields[] = 'custom_description';
        }

        $status = $missingFields === [] ? 'ready' : 'needs_clarification';
        $message = $this->responseMessage($fields['service_type'], $missingFields, $isCustomQuote);

        return [
            'status' => $status,
            'message' => $message,
            'fields' => $fields,
            'missing_fields' => $missingFields,
        ];
    }

    private function extractUrgency(string $description): ?string
    {
        return match (true) {
            preg_match('/\b(sans urgence|pas presse|flexibles?|quand vous pouvez|le mois prochain)\b/u', $description) === 1 => 'flexible',
            preg_match('/\b(urgents?|urgentes?|urgence|au plus vite|des que possible|cette semaine|immediatement)\b/u', $description) === 1 => 'urgent',
            preg_match('/\b(rapidement|bientot|dans (?:\d+|un|une|deux|trois|quatre|cinq) jours?|dans (?:\d+|un|une|deux|trois|quatre|cinq) semaines?)\b/u', $description) === 1 => 'soon',
            default => null,
        };
    }

    private function extractNumber(string $description, string $pattern): ?float
    {
        if (preg_match($pattern, $description, $matches) !== 1) {
            return null;
        }

        return (float) str_replace(',', '.', $matches[1]);
    }

    private function extractAddress(string $description): ?string
    {
        if (preg_match('/\badresse\s*[:=-]\s*(.+?)(?=\s*,?\s*(?:surface|budget|montant|urgence|priorite)\b|$)/iu', $description, $matches) !== 1) {
            return null;
        }

        $address = trim($matches[1], " \t\n\r\0\x0B,.;");

        return $address === '' ? null : $address;
    }

    private function extractCity(?string $address): ?string
    {
        if ($address === null || ! str_contains($address, ',')) {
            return null;
        }

        $city = trim((string) strrchr($address, ','), " \t\n\r\0\x0B,.;");

        return $city === '' ? null : $city;
    }

    private function detectKnownCity(string $normalized): ?string
    {
        $cities = [
            'ben arous' => 'Ben Arous', 'sidi bouzid' => 'Sidi Bouzid', 'la marsa' => 'La Marsa',
            'le kef' => 'Le Kef', 'tunis' => 'Tunis', 'ariana' => 'Ariana', 'manouba' => 'Manouba',
            'nabeul' => 'Nabeul', 'hammamet' => 'Hammamet', 'zaghouan' => 'Zaghouan',
            'bizerte' => 'Bizerte', 'beja' => 'Béja', 'jendouba' => 'Jendouba',
            'siliana' => 'Siliana', 'sousse' => 'Sousse', 'monastir' => 'Monastir',
            'mahdia' => 'Mahdia', 'sfax' => 'Sfax', 'kairouan' => 'Kairouan',
            'kasserine' => 'Kasserine', 'gabes' => 'Gabès', 'medenine' => 'Médenine',
            'djerba' => 'Djerba', 'tataouine' => 'Tataouine', 'gafsa' => 'Gafsa',
            'tozeur' => 'Tozeur', 'kebili' => 'Kébili', 'carthage' => 'Carthage',
        ];

        foreach ($cities as $needle => $label) {
            if (preg_match('/\b'.preg_quote($needle, '/').'\b/u', $normalized) === 1) {
                return $label;
            }
        }

        return null;
    }

    private function extractSurface(string $normalized): ?float
    {
        $pattern = '/\b(\d+(?:[.,]\d+)?)\s*(?:m2|m\s*2|metres?\s+carres?|mc)\b/u';

        return $this->extractNumber($normalized, $pattern);
    }

    private function extractBudget(string $normalized): ?float
    {
        // Retire les surfaces pour ne pas les confondre avec un budget.
        $text = preg_replace('/\b\d+(?:[.,]\d+)?\s*(?:m2|m\s*2|metres?\s+carres?|mc)\b/u', ' ', $normalized) ?? $normalized;

        // Espaces entre milliers : "1 500" devient "1500".
        $text = preg_replace('/(?<=\d)\s(?=\d{3}\b)/u', '', $text) ?? $text;

        // 1) Montant apres un mot-cle : "budget 2k", "budget de 800 tnd".
        if (preg_match('/\b(?:budget|montant|enveloppe)(?:\s+(?:de|maximum|max|d))?\s*[:=]?\s*(\d+(?:[.,]\d+)?)\s*(k|mille)?\s*(?:tnd|dt|dinars?)?/u', $text, $m) === 1) {
            return $this->toAmount($m[1], $m[2] ?? '');
        }

        // 2) Montant suivi d'une devise : "800 TND", "2000 dinars", "1.5k dt".
        if (preg_match('/\b(\d+(?:[.,]\d+)?)\s*(k|mille)?\s*(?:tnd|dt|dinars?)\b/u', $text, $m) === 1) {
            return $this->toAmount($m[1], $m[2] ?? '');
        }

        return null;
    }

    private function toAmount(string $number, string $suffix): float
    {
        $value = (float) str_replace(',', '.', $number);

        return $suffix !== '' ? $value * 1000 : $value;
    }

    /**
     * Re-evaluate a set of fields (for example after the AI filled some gaps).
     *
     * @param  array<string, mixed>  $fields
     * @return array{status: string, message: string, missing_fields: list<string>}
     */
    public function evaluate(array $fields): array
    {
        $missingFields = [];

        foreach (['service_type', 'urgency', 'budget', 'address', 'city'] as $requiredField) {
            if (($fields[$requiredField] ?? null) === null) {
                $missingFields[] = $requiredField;
            }
        }

        $isCustomQuote = ($fields['service_type'] ?? null) === 'custom_quote';

        if ($isCustomQuote && blank($fields['custom_description'] ?? null)) {
            $missingFields[] = 'custom_description';
        }

        return [
            'status' => $missingFields === [] ? 'ready' : 'needs_clarification',
            'message' => $this->responseMessage($fields['service_type'] ?? null, $missingFields, $isCustomQuote),
            'missing_fields' => $missingFields,
        ];
    }

    /** @param list<string> $missingFields */
    private function responseMessage(?string $serviceType, array $missingFields, bool $isCustomQuote): string
    {
        if ($isCustomQuote) {
            return __('This service is not in the current catalog. I have marked it for a custom quote; please review the description and complete the missing details.');
        }

        if ($serviceType === null) {
            return __('I could not identify the requested service. Please clarify what work you need, or choose a custom quote if it is outside the catalog.');
        }

        if ($missingFields !== []) {
            return __('I have identified a possible service. Please complete the highlighted details and correct anything that is inaccurate.');
        }

        return __('Here is the request I understood. Please review or correct every field before sending it.');
    }
}
