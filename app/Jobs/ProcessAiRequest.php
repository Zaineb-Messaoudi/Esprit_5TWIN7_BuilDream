<?php

namespace App\Jobs;

use App\Services\AiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAiRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 5; // seconds
    public int $timeout = 180; // 3 minutes for slow LLM calls

    public function __construct(
        public string $endpoint,
        public array $payload,
        public string $callbackUrl = '',
        public string $callbackSecret = ''
    ) {}

    public function handle(AiService $ai): void
    {
        $startTime = microtime(true);
        
        try {
            $result = match ($this->endpoint) {
                'natural-language-search' => $this->handleNaturalLanguageSearch($ai),
                'generate-description' => $this->handleGenerateDescription($ai),
                'suggest-price' => $this->handleSuggestPrice($ai),
                'auto-categorize' => $this->handleAutoCategorize($ai),
                'chatbot' => $this->handleChatbot($ai),
                'semantic-search' => $this->handleSemanticSearch($ai),
                'validate-image' => $this->handleValidateImage($ai),
                default => ['error' => 'Unknown endpoint'],
            };

            $this->sendCallback($result, null, $startTime);
            
        } catch (\Throwable $e) {
            Log::error('AI Job failed', [
                'endpoint' => $this->endpoint,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);
            
            $this->sendCallback(null, $e->getMessage(), $startTime);
            
            if ($this->attempts() >= $this->tries) {
                throw $e; // Mark as failed permanently
            }
            
            throw $e; // Retry
        }
    }

    private function handleNaturalLanguageSearch(AiService $ai): array
    {
        $query = $this->payload['q'] ?? '';
        if (!$query || !$ai->isConfigured()) {
            return ['error' => 'AI not configured or query empty'];
        }

        $systemPrompt = <<<'PROMPT'
You are an AI assistant for a renewable energy equipment rental marketplace called SolarShare.
Convert the user's natural language query into a JSON filter object for equipment search.

Available equipment fields:
- category: string (e.g., "battery", "solar panel", "inverter", "generator", "charger")
- brand: string
- model: string
- price_per_day: number (TND)
- condition: "new" | "good" | "fair" | "damaged"
- location: string (city name in Tunisia)
- status: "available" | "unavailable" | "maintenance"
- energyProfile: {
    power_watts: number,
    capacity_wh: number,
    voltage: number,
    efficiency: number,
    technology: string,
    max_output: number,
    operating_duration: number
  }

Return ONLY a JSON object with these possible keys:
- category (string)
- brand (string)
- min_price (number)
- max_price (number)
- condition (string)
- location (string)
- min_power (number)
- max_power (number)
- min_capacity (number)
- max_capacity (number)
- technology (string)

Only include keys that are relevant to the user's query. Use camelCase for keys.
PROMPT;

        $filters = $ai->chatJson([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $query],
        ]);

        if (!$filters) {
            return ['error' => 'Failed to parse query'];
        }

        $equipmentQuery = \App\Models\Equipment::query()
            ->where('approval_status', 'published')
            ->where('status', 'available')
            ->with(['category', 'energyProfile']);

        $this->applyFilters($equipmentQuery, $filters);
        $results = $equipmentQuery->limit(10)->get();

        return [
            'query' => $query,
            'filters' => $filters,
            'results' => $results->map(fn ($e) => [
                'id' => $e->id,
                'name' => $e->name,
                'brand' => $e->brand,
                'model' => $e->model,
                'price_per_day' => $e->price_per_day,
                'category' => $e->category?->name,
                'location' => $e->location,
                'energyProfile' => $e->energyProfile ? [
                    'power_watts' => $e->energyProfile->power_watts,
                    'capacity_wh' => $e->energyProfile->capacity_wh,
                    'technology' => $e->energyProfile->technology,
                ] : null,
            ]),
        ];
    }

    private function handleGenerateDescription(AiService $ai): array
    {
        $name = $this->payload['name'] ?? '';
        $brand = $this->payload['brand'] ?? '';
        $model = $this->payload['model'] ?? '';
        $category = $this->payload['category'] ?? '';
        $energy = $this->payload['energy_profile'] ?? [];

        if (!$ai->isConfigured()) {
            return ['error' => 'AI not configured'];
        }

        $systemPrompt = <<<'PROMPT'
You are an AI assistant for SolarShare, a renewable energy equipment rental marketplace.
Generate a compelling, professional equipment description for a rental listing.
The description should be in French, highlight key technical specifications, 
mention ideal use cases, and be inviting for potential renters.
Keep it under 300 words. Use a professional but friendly tone.
PROMPT;

        $userPrompt = "Generate a description for:\n";
        $userPrompt .= "- Name: {$name}\n";
        $userPrompt .= "- Brand: {$brand}\n";
        $userPrompt .= "- Model: {$model}\n";
        $userPrompt .= "- Category: {$category}\n";
        
        if ($energy) {
            $userPrompt .= "\nTechnical specs:\n";
            foreach ($energy as $key => $value) {
                if ($value) {
                    $userPrompt .= "- {$key}: {$value}\n";
                }
            }
        }

        $description = $ai->chat([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ]);

        return ['description' => $description ?? 'Unable to generate description. Please try again.'];
    }

    private function handleSuggestPrice(AiService $ai): array
    {
        $category = $this->payload['category'] ?? '';
        $brand = $this->payload['brand'] ?? null;
        $powerWatts = $this->payload['power_watts'] ?? null;
        $capacityWh = $this->payload['capacity_wh'] ?? null;

        if (!$ai->isConfigured()) {
            return ['error' => 'AI not configured'];
        }

        $similarQuery = \App\Models\Equipment::query()
            ->where('approval_status', 'published')
            ->where('status', 'available')
            ->whereHas('category', fn ($q) => $q->where('name', 'like', "%{$category}%"));

        if ($brand) $similarQuery->where('brand', $brand);
        if ($powerWatts) $similarQuery->whereHas('energyProfile', fn ($q) => $q->where('power_watts', $powerWatts));
        if ($capacityWh) $similarQuery->whereHas('energyProfile', fn ($q) => $q->where('capacity_wh', $capacityWh));

        $similar = $similarQuery->get(['price_per_day']);
        $prices = $similar->pluck('price_per_day')->filter()->values();
        $medianPrice = $prices->isEmpty() ? null : $prices->sort()->median();

        $systemPrompt = <<<'PROMPT'
You are a pricing advisor for SolarShare, a renewable energy equipment rental marketplace.
Given the median price of similar equipment and the equipment specs, provide a suggested daily rental price in TND.
Explain your reasoning briefly (2-3 sentences). Return JSON with "suggested_price" (number) and "explanation" (string).
PROMPT;

        $userPrompt = "Equipment details:\n";
        $userPrompt .= "- Category: {$category}\n";
        if ($brand) $userPrompt .= "- Brand: {$brand}\n";
        if ($powerWatts) $userPrompt .= "- Power: {$powerWatts}W\n";
        if ($capacityWh) $userPrompt .= "- Capacity: {$capacityWh}Wh\n";
        $userPrompt .= "- Median price of similar equipment: " . ($medianPrice ? "{$medianPrice} TND/day" : "No data available") . "\n";

        $result = $ai->chatJson([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ]);

        return [
            'median_price' => $medianPrice,
            'sample_size' => $prices->count(),
            'suggestion' => $result ?? [
                'suggested_price' => $medianPrice,
                'explanation' => 'Based on median price of similar equipment in the marketplace.',
            ],
        ];
    }

    private function handleAutoCategorize(AiService $ai): array
    {
        $name = $this->payload['name'] ?? '';
        $description = $this->payload['description'] ?? '';

        if (!$ai->isConfigured()) {
            return ['error' => 'AI not configured'];
        }

        $categories = \App\Models\Category::where('status', 'active')->pluck('name')->toArray();
        $categoriesList = implode(', ', $categories);

        $systemPrompt = "You are an AI assistant for SolarShare. Given an equipment name and description, suggest the most appropriate category from this list: {$categoriesList}. Return JSON with 'category' (string) and 'confidence' (0-100).";

        $result = $ai->chatJson([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => "Name: {$name}\nDescription: {$description}"],
        ]);

        return $result ?? ['category' => $categories[0] ?? 'Battery', 'confidence' => 50];
    }

    private function handleChatbot(AiService $ai): array
    {
        $message = $this->payload['message'] ?? '';
        $equipmentIds = $this->payload['context_equipment_ids'] ?? [];

        if (!$ai->isConfigured()) {
            return ['error' => 'AI not configured'];
        }

        $equipment = \App\Models\Equipment::with(['category', 'energyProfile'])
            ->whereIn('id', $equipmentIds)
            ->where('approval_status', 'published')
            ->where('status', 'available')
            ->limit(10)
            ->get();

        $context = $equipment->map(fn ($e) => [
            'id' => $e->id,
            'name' => $e->name,
            'brand' => $e->brand,
            'model' => $e->model,
            'category' => $e->category?->name,
            'price_per_day' => $e->price_per_day,
            'location' => $e->location,
            'condition' => $e->condition,
            'energyProfile' => $e->energyProfile ? [
                'power_watts' => $e->energyProfile->power_watts,
                'capacity_wh' => $e->energyProfile->capacity_wh,
                'voltage' => $e->energyProfile->voltage,
                'efficiency' => $e->energyProfile->efficiency,
                'technology' => $e->energyProfile->technology,
            ] : null,
        ])->toArray();

        $systemPrompt = <<<'PROMPT'
You are a helpful equipment advisor for SolarShare, a renewable energy equipment rental marketplace.
Answer the user's question ONLY using the equipment data provided below.
Do NOT invent specifications, prices, or availability. If the information is not in the data, say you don't have that information.
Be concise and helpful. Respond in French.
PROMPT;

        $response = $ai->chat([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => "Available equipment:\n" . json_encode($context, JSON_PRETTY_PRINT) . "\n\nUser question: {$this->payload['message']}"],
        ]);

        return ['response' => $response ?? "I'm unable to answer right now. Please try again."];
    }

    private function handleSemanticSearch(AiService $ai): array
    {
        $query = $this->payload['q'] ?? '';
        
        if (!$query || !$ai->isConfigured()) {
            return ['error' => 'AI not configured or query empty'];
        }

        $result = $ai->semanticSearchJson($query, 10);
        
        if (!$result) {
            return ['error' => 'Failed to generate embedding'];
        }

        return [
            'query' => $this->payload['q'],
            'note' => 'Using JSON embedding similarity (MySQL-compatible). For >10k rows, use pgvector or Qdrant.',
            ...$result,
        ];
    }

    private function handleValidateImage(AiService $ai): array
    {
        if (!$ai->isConfigured()) {
            return ['error' => 'AI not configured'];
        }

        // Image validation requires vision model - handled differently
        return ['error' => 'Image validation not supported in async mode'];
    }

    private function applyFilters($query, array $filters): void
    {
        if (isset($filters['category'])) $query->whereHas('category', fn ($q) => $q->where('name', 'like', "%{$filters['category']}%"));
        if (isset($filters['brand'])) $query->where('brand', 'like', "%{$filters['brand']}%");
        if (isset($filters['min_price'])) $query->where('price_per_day', '>=', $filters['min_price']);
        if (isset($filters['max_price'])) $query->where('price_per_day', '<=', $filters['max_price']);
        if (isset($filters['condition'])) $query->where('condition', $filters['condition']);
        if (isset($filters['location'])) $query->where('location', 'like', "%{$filters['location']}%");
        if (isset($filters['min_power'])) $query->whereHas('energyProfile', fn ($q) => $q->where('power_watts', '>=', $filters['min_power']));
        if (isset($filters['max_power'])) $query->whereHas('energyProfile', fn ($q) => $q->where('power_watts', '<=', $filters['max_power']));
        if (isset($filters['min_capacity'])) $query->whereHas('energyProfile', fn ($q) => $q->where('capacity_wh', '>=', $filters['min_capacity']));
        if (isset($filters['max_capacity'])) $query->whereHas('energyProfile', fn ($q) => $q->where('capacity_wh', '<=', $filters['max_capacity']));
        if (isset($filters['technology'])) $query->whereHas('energyProfile', fn ($q) => $q->where('technology', 'like', "%{$filters['technology']}%"));
    }

    private function sendCallback(array $result, ?string $error, float $startTime): void
    {
        if (!$this->callbackUrl) return;

        $duration = round((microtime(true) - $startTime) * 1000);

        try {
            \Illuminate\Support\Facades\Http::timeout(10)->withHeaders([
                'X-Callback-Secret' => $this->callbackSecret,
            ])->post($this->callbackUrl, [
                'endpoint' => $this->endpoint,
                'success' => $error === null,
                'result' => $result,
                'error' => $error,
                'duration_ms' => $duration,
            ]);
        } catch (\Throwable) {
            // Callback failed silently
        }
    }
}