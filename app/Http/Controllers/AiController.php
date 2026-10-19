<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessAiRequest;
use App\Models\Equipment;
use App\Services\AiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiController extends Controller
{
    public function __construct(private readonly AiService $ai) {}

    /**
     * Sanitize user input to prevent prompt injection.
     * Removes/neutralizes common injection patterns.
     */
    private function sanitize(string $input, int $maxLength = 2000): string
    {
        $input = trim($input);
        $input = Str::limit($input, $maxLength, '');
        
        // Remove potential injection patterns
        $patterns = [
            '/ignore\s+(previous|above|system)\s+(instructions?|prompts?)/i',
            '/forget\s+(everything|all|previous)/i',
            '/you\s+are\s+now\s+/i',
            '/system\s*:\s*/i',
            '/assistant\s*:\s*/i',
            '/```/i',  // Code blocks
            '/<\|.*?\|>/',  // Special tokens
        ];
        
        foreach ($patterns as $pattern) {
            $input = preg_replace($pattern, '[filtered]', $input);
        }
        
        // Limit excessive whitespace
        $input = preg_replace('/\s{3,}/', '  ', $input);
        
        return $input;
    }

    /**
     * Dispatch AI request to queue for async processing.
     * Returns job ID immediately, result delivered via webhook/callback.
     */
    private function dispatchAsync(string $endpoint, array $payload, Request $request): JsonResponse
    {
        $callbackUrl = $request->input('callback_url');
        $callbackSecret = $request->input('callback_secret', Str::random(32));
        
        $job = new ProcessAiRequest($endpoint, $payload, $callbackUrl, $callbackSecret);
        $jobId = $job->dispatch()->getJobId();
        
        return response()->json([
            'job_id' => $jobId,
            'status' => 'queued',
            'message' => 'Request queued for async processing. Provide callback_url for result.',
            'callback_secret' => $callbackSecret,
        ], 202);
    }

    public function naturalLanguageSearch(Request $request): JsonResponse
    {
        // Check if async mode requested
        if ($request->boolean('async')) {
            return $this->dispatchAsync('natural-language-search', ['q' => $request->string('q')->trim()], $request);
        }
        
        $query = $this->sanitize($request->string('q')->trim());
        
        if (!$query || !$this->ai->isConfigured()) {
            return response()->json(['error' => 'AI not configured or query empty'], 400);
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

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $query],
        ];

        $filters = $this->ai->chatJson($messages);

        if (!$filters) {
            return response()->json(['error' => 'Failed to parse query'], 500);
        }

        // Build Eloquent query from filters
        $equipmentQuery = Equipment::query()
            ->where('approval_status', 'published')
            ->where('status', 'available')
            ->with(['category', 'energyProfile']);

        $this->applyFilters($equipmentQuery, $filters);

        $results = $equipmentQuery->limit(10)->get();

        return response()->json([
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
        ]);
    }

    public function generateDescription(Request $request): JsonResponse
    {
        if ($request->boolean('async')) {
            return $this->dispatchAsync('generate-description', [
                'name' => $request->name,
                'brand' => $request->brand,
                'model' => $request->model,
                'category' => $request->category,
                'energy_profile' => $request->input('energy_profile', []),
            ], $request);
        }
        
        $request->validate([
            'name' => 'required|string',
            'brand' => 'required|string',
            'model' => 'required|string',
            'category' => 'required|string',
            'energy_profile' => 'array',
        ]);

        if (!$this->ai->isConfigured()) {
            return response()->json(['error' => 'AI not configured'], 400);
        }

        $energy = $request->input('energy_profile', []);
        
        // Sanitize all user inputs
        $name = $this->sanitize($request->name);
        $brand = $this->sanitize($request->brand);
        $model = $this->sanitize($request->model);
        $category = $this->sanitize($request->category);
        
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
        $userPrompt .= "- Model: {$request->model}\n";
        $userPrompt .= "- Category: {$request->category}\n";
        
        if ($energy) {
            $userPrompt .= "\nTechnical specs:\n";
            foreach ($energy as $key => $value) {
                if ($value) {
                    $userPrompt .= "- {$key}: {$value}\n";
                }
            }
        }

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ];

        $description = $this->ai->chat($messages);

        return response()->json([
            'description' => $description ?? 'Unable to generate description. Please try again.',
        ]);
    }

    public function suggestPrice(Request $request): JsonResponse
    {
        if ($request->boolean('async')) {
            return $this->dispatchAsync('suggest-price', [
                'category' => $request->category,
                'brand' => $request->brand,
                'power_watts' => $request->power_watts,
                'capacity_wh' => $request->capacity_wh,
            ], $request);
        }
        
        $request->validate([
            'category' => 'required|string',
            'brand' => 'nullable|string',
            'power_watts' => 'nullable|integer',
            'capacity_wh' => 'nullable|integer',
        ]);

        if (!$this->ai->isConfigured()) {
            return response()->json(['error' => 'AI not configured'], 400);
        }

        // Sanitize inputs
        $category = $this->sanitize($request->category);
        $brand = $request->filled('brand') ? $this->sanitize($request->brand) : null;
        
        // Get median price from similar equipment
        $similarQuery = Equipment::query()
            ->where('approval_status', 'published')
            ->where('status', 'available')
            ->whereHas('category', fn ($q) => $q->where('name', 'like', "%{$category}%"));

        if ($request->filled('brand')) {
            $similarQuery->where('brand', $request->brand);
        }

        if ($request->filled('power_watts')) {
            $similarQuery->whereHas('energyProfile', fn ($q) => $q->where('power_watts', $request->power_watts));
        }

        if ($request->filled('capacity_wh')) {
            $similarQuery->whereHas('energyProfile', fn ($q) => $q->where('capacity_wh', $request->capacity_wh));
        }

        $similar = $similarQuery->get(['price_per_day']);
        $prices = $similar->pluck('price_per_day')->filter()->values();
        
        $medianPrice = $prices->isEmpty() ? null : $prices->sort()->median();

        $systemPrompt = <<<'PROMPT'
You are a pricing advisor for SolarShare, a renewable energy equipment rental marketplace.
Given the median price of similar equipment and the equipment specs, provide a suggested daily rental price in TND.
Explain your reasoning briefly (2-3 sentences). Return JSON with "suggested_price" (number) and "explanation" (string).
PROMPT;

        $userPrompt = "Equipment details:\n";
        $userPrompt .= "- Category: {$request->category}\n";
        if ($request->filled('brand')) $userPrompt .= "- Brand: {$request->brand}\n";
        if ($request->filled('power_watts')) $userPrompt .= "- Power: {$request->power_watts}W\n";
        if ($request->filled('capacity_wh')) $userPrompt .= "- Capacity: {$request->capacity_wh}Wh\n";
        $userPrompt .= "- Median price of similar equipment: " . ($medianPrice ? "{$medianPrice} TND/day" : "No data available") . "\n";

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ];

        $result = $this->ai->chatJson($messages);

        return response()->json([
            'median_price' => $medianPrice,
            'sample_size' => $prices->count(),
            'suggestion' => $result ?? [
                'suggested_price' => $medianPrice,
                'explanation' => 'Based on median price of similar equipment in the marketplace.',
            ],
        ]);
    }

    public function autoCategorize(Request $request): JsonResponse
    {
        if ($request->boolean('async')) {
            return $this->dispatchAsync('auto-categorize', [
                'name' => $request->name,
                'description' => $request->description,
            ], $request);
        }
        
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        if (!$this->ai->isConfigured()) {
            return response()->json(['error' => 'AI not configured'], 400);
        }

        // Sanitize inputs
        $name = $this->sanitize($request->name);
        $description = $request->filled('description') ? $this->sanitize($request->description) : '';
        
        $categories = \App\Models\Category::where('status', 'active')->pluck('name')->toArray();
        $categoriesList = implode(', ', $categories);

        $systemPrompt = "You are an AI assistant for SolarShare. Given an equipment name and description, suggest the most appropriate category from this list: {$categoriesList}. Return JSON with 'category' (string) and 'confidence' (0-100).";

        $userPrompt = "Name: {$name}\nDescription: {$description}";

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ];

        $result = $this->ai->chatJson($messages);

        return response()->json($result ?? [
            'category' => $categories[0] ?? 'Battery',
            'confidence' => 50,
        ]);
    }

    public function chatbot(Request $request): JsonResponse
    {
        if ($request->boolean('async')) {
            return $this->dispatchAsync('chatbot', [
                'message' => $request->string('message')->trim(),
                'context_equipment_ids' => $request->input('context_equipment_ids', []),
            ], $request);
        }
        
        $request->validate([
            'message' => 'required|string',
            'context_equipment_ids' => 'array',
        ]);

        if (!$this->ai->isConfigured()) {
            return response()->json(['error' => 'AI not configured'], 400);
        }

        // Sanitize user message
        $message = $this->sanitize($request->string('message')->trim());

        $equipmentIds = $request->input('context_equipment_ids', []);
        $equipment = Equipment::with(['category', 'energyProfile'])
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

        $userPrompt = "Available equipment:\n" . json_encode($context, JSON_PRETTY_PRINT) . "\n\nUser question: {$message}";

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ];

        $response = $this->ai->chat($messages);

        return response()->json([
            'response' => $response ?? "I'm unable to answer right now. Please try again.",
        ]);
    }

    public function semanticSearch(Request $request): JsonResponse
    {
        if ($request->boolean('async')) {
            return $this->dispatchAsync('semantic-search', ['q' => $request->string('q')->trim()], $request);
        }
        
        $query = $this->sanitize($request->string('q')->trim());
        
        if (!$query || !$this->ai->isConfigured()) {
            return response()->json(['error' => 'AI not configured or query empty'], 400);
        }

        // Use JSON-based semantic search (works on MySQL/PostgreSQL)
        $result = $this->ai->semanticSearchJson($query, 10);
        
        if (!$result) {
            return response()->json(['error' => 'Failed to generate embedding'], 500);
        }

        return response()->json([
            'query' => $query,
            'note' => 'Using JSON embedding similarity (MySQL-compatible). For >10k rows, use pgvector or Qdrant.',
            ...$result,
        ]);
    }

    public function validateImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|max:5120',
            'equipment_name' => 'required|string',
            'category' => 'required|string',
        ]);

        if (!$this->ai->isConfigured() || $this->ai->provider !== 'openai') {
            return response()->json([
                'valid' => true,
                'note' => 'Image validation requires OpenAI with vision model. Skipped.',
            ]);
        }

        $image = $request->file('image');
        $base64 = base64_encode(file_get_contents($image->getRealPath()));
        $mime = $image->getMimeType();

        $messages = [
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => "Does this image show a {$request->category} called '{$request->equipment_name}'? Answer with JSON: {valid: boolean, confidence: 0-100, reason: string}. Be strict - only validate if the image clearly shows the equipment type mentioned.",
                    ],
                    [
                        'type' => 'image_url',
                        'image_url' => ['url' => "data:{$mime};base64,{$base64}"],
                    ],
                ],
            ],
        ];

        $result = $this->ai->chatJson($messages, ['model' => 'gpt-4o-mini', 'max_tokens' => 500]);

        return response()->json($result ?? [
            'valid' => true,
            'confidence' => 50,
            'reason' => 'Unable to validate image',
        ]);
    }

    private function applyFilters($query, array $filters): void
    {
        if (isset($filters['category'])) {
            $query->whereHas('category', fn ($q) => $q->where('name', 'like', "%{$filters['category']}%"));
        }
        if (isset($filters['brand'])) {
            $query->where('brand', 'like', "%{$filters['brand']}%");
        }
        if (isset($filters['min_price'])) {
            $query->where('price_per_day', '>=', $filters['min_price']);
        }
        if (isset($filters['max_price'])) {
            $query->where('price_per_day', '<=', $filters['max_price']);
        }
        if (isset($filters['condition'])) {
            $query->where('condition', $filters['condition']);
        }
        if (isset($filters['location'])) {
            $query->where('location', 'like', "%{$filters['location']}%");
        }
        if (isset($filters['min_power'])) {
            $query->whereHas('energyProfile', fn ($q) => $q->where('power_watts', '>=', $filters['min_power']));
        }
        if (isset($filters['max_power'])) {
            $query->whereHas('energyProfile', fn ($q) => $q->where('power_watts', '<=', $filters['max_power']));
        }
        if (isset($filters['min_capacity'])) {
            $query->whereHas('energyProfile', fn ($q) => $q->where('capacity_wh', '>=', $filters['min_capacity']));
        }
        if (isset($filters['max_capacity'])) {
            $query->whereHas('energyProfile', fn ($q) => $q->where('capacity_wh', '<=', $filters['max_capacity']));
        }
        if (isset($filters['technology'])) {
            $query->whereHas('energyProfile', fn ($q) => $q->where('technology', 'like', "%{$filters['technology']}%"));
        }
    }
}