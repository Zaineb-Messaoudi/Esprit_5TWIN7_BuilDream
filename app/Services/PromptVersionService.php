<?php

namespace App\Services;

use App\Models\AiPrompt;
use Illuminate\Support\Facades\Log;

class PromptVersionService
{
    /**
     * Register a prompt for an endpoint and return the prompt record.
     * This should be called on app boot to register all known prompts.
     */
    public function registerEndpointPrompts(): void
    {
        // Natural Language Search
        AiPrompt::register(
            'natural-language-search',
            <<<'PROMPT'
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
PROMPT,
            '{query}',
            ['query']
        );

        // Description Generator
        AiPrompt::register(
            'generate-description',
            <<<'PROMPT'
You are an AI assistant for SolarShare, a renewable energy equipment rental marketplace.
Generate a compelling, professional equipment description for a rental listing.
The description should be in French, highlight key technical specifications, 
mention ideal use cases, and be inviting for potential renters.
Keep it under 300 words. Use a professional but friendly tone.
PROMPT,
            "Generate a description for:\n- Name: {name}\n- Brand: {brand}\n- Model: {model}\n- Category: {category}\n\nTechnical specs:\n{energy_specs}",
            ['name', 'brand', 'model', 'category', 'energy_specs']
        );

        // Price Suggestion
        AiPrompt::register(
            'suggest-price',
            <<<'PROMPT'
You are a pricing advisor for SolarShare, a renewable energy equipment rental marketplace.
Given the median price of similar equipment and the equipment specs, provide a suggested daily rental price in TND.
Explain your reasoning briefly (2-3 sentences). Return JSON with "suggested_price" (number) and "explanation" (string).
PROMPT,
            "Equipment details:\n- Category: {category}\n{brand_line}{power_line}{capacity_line}- Median price of similar equipment: {median_price}\n",
            ['category', 'brand_line', 'power_line', 'capacity_line', 'median_price']
        );

        // Auto-categorize
        AiPrompt::register(
            'auto-categorize',
            'You are an AI assistant for SolarShare. Given an equipment name and description, suggest the most appropriate category from this list: {categories_list}. Return JSON with "category" (string) and "confidence" (0-100).',
            'Name: {name}\nDescription: {description}',
            ['categories_list', 'name', 'description']
        );

        // Chatbot
        AiPrompt::register(
            'chatbot',
            <<<'PROMPT'
You are a helpful equipment advisor for SolarShare, a renewable energy equipment rental marketplace.
Answer the user's question ONLY using the equipment data provided below.
Do NOT invent specifications, prices, or availability. If the information is not in the data, say you don't have that information.
Be concise and helpful. Respond in French.
PROMPT,
            'Available equipment:\n{context}\n\nUser question: {message}',
            ['context', 'message']
        );

        // Semantic Search
        AiPrompt::register(
            'semantic-search',
            'You are an AI assistant for SolarShare. Convert the user query into a JSON filter object for equipment search. Return ONLY JSON.',
            '{query}',
            ['query']
        );

        // Image Validation
        AiPrompt::register(
            'validate-image',
            'Does this image show a {category} called "{equipment_name}"? Answer with JSON: {valid: boolean, confidence: 0-100, reason: string}. Be strict - only validate if the image clearly shows the equipment type mentioned.',
            '{image}',
            ['category', 'equipment_name', 'image']
        );
    }

    /**
     * Get the active prompt for an endpoint.
     */
    public function getPrompt(string $endpoint): ?\App\Models\AiPrompt
    {
        return \App\Models\AiPrompt::where('endpoint', $endpoint)
            ->where('is_active', true)
            ->latest('last_used_at')
            ->first();
    }

    /**
     * Get the prompt with variables filled in.
     */
    public function getPromptWithVariables(string $endpoint, array $variables): ?array
    {
        $prompt = $this->getPrompt($endpoint);
        if (!$prompt) {
            return null;
        }

        $prompt->incrementUsage();
        return $prompt->getPromptWithVariables($variables);
    }

    /**
     * Log prompt usage for analytics.
     */
    public function logUsage(string $endpoint, string $promptHash, bool $success, int $latencyMs): void
    {
        Log::channel('ai')->info('AI prompt executed', [
            'endpoint' => $endpoint,
            'prompt_hash' => $promptHash,
            'success' => $success,
            'latency_ms' => $latencyMs,
            'timestamp' => now()->toISOString(),
        ]);
    }
}