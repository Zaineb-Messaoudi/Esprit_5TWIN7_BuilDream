<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

/**
 * Admin controller for AI/LLM configuration.
 * Allows administrators to configure the AI provider, model, and connection settings.
 */
class AdminAiSettingsController extends Controller
{
    public function __construct(private readonly AiService $ai) {}

    /**
     * Display the AI configuration page.
     */
    public function index(): View
    {
        $config = config('ai');
        $ollamaHealth = null;

        if ($config['provider'] === 'ollama') {
            $ollamaHealth = $this->ai->checkOllamaHealth();
        }

        $availableModels = $this->getAvailableModels($config['provider']);
        $visionModels = $this->getAvailableVisionModels($config['provider']);

        // Format models for JavaScript
        $availableModelsFormatted = array_map(
            fn ($label, $value) => ['value' => $value, 'label' => $label],
            array_keys($availableModels),
            $availableModels
        );

        $visionModelsFormatted = array_map(
            fn ($label, $value) => ['value' => $value, 'label' => $label],
            array_keys($visionModels),
            $visionModels
        );

        return view('pages.admin.ai-settings', [
            'title' => __('AI Configuration'),
            'config' => $config,
            'ollamaHealth' => $ollamaHealth,
            'availableModels' => $availableModels,
            'availableModelsFormatted' => $availableModelsFormatted,
            'visionModels' => $visionModels,
            'visionModelsFormatted' => $visionModelsFormatted,
            'providers' => [
                'ollama' => 'Ollama (Local, FREE)',
                'openai' => 'OpenAI (Cloud, Paid)',
                'anthropic' => 'Anthropic (Cloud, Paid)',
            ],
        ]);
    }

    /**
     * Update AI configuration.
     * Settings are saved to .env file for persistence.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'provider' => ['required', 'in:ollama,openai,anthropic'],
            'base_url' => ['nullable', 'url'],
            'model' => ['required', 'string', 'max:100'],
            'embedding_model' => ['nullable', 'string', 'max:100'],
            'api_key' => ['nullable', 'string'],
            'temperature' => ['nullable', 'numeric', 'between:0,2'],
            'max_tokens' => ['nullable', 'integer', 'min:1', 'max:8192'],
        ]);

        $this->updateEnvFile($validated);

        // Clear config cache so changes take effect immediately
        \Artisan::call('config:clear');

        return back()->with('status', __('AI settings updated successfully. Configuration cache cleared.'));
    }

    /**
     * Test the AI connection.
     */
    public function testConnection(): \Illuminate\Http\JsonResponse
    {
        if ($this->ai->getProvider() === 'ollama') {
            $health = $this->ai->checkOllamaHealth();

            return response()->json($health);
        }

        // For cloud providers, try a simple chat completion
        $testResponse = $this->ai->chat([
            ['role' => 'user', 'content' => 'Say "OK" if you receive this.'],
        ], ['max_tokens' => 10]);

        $success = $testResponse !== null && stripos($testResponse, 'ok') !== false;

        return response()->json([
            'available' => $success,
            'provider' => $this->ai->getProvider(),
            'model' => $this->ai->getModel(),
            'response' => $testResponse ? substr($testResponse, 0, 100) : null,
        ]);
    }

    /**
     * Pull a model in Ollama.
     */
    public function pullModel(Request $request): \Illuminate\Http\JsonResponse
    {
        if ($this->ai->getProvider() !== 'ollama') {
            return response()->json(['success' => false, 'message' => 'Only available for Ollama'], 400);
        }

        $request->validate(['model' => ['required', 'string']]);

        try {
            $url = rtrim($this->ai->getBaseUrl(), '/').'/api/pull';
            $response = Http::timeout(300)->post($url, [
                'model' => $request->model,
                'stream' => false,
            ]);

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to pull model: '.$response->body(),
                ], 500);
            }

            return response()->json(['success' => true, 'message' => 'Model pulled successfully']);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available models for the current provider.
     */
    private function getAvailableModels(string $provider): array
    {
        return match ($provider) {
            'ollama' => [
                'llama3.2' => 'Llama 3.2 (3B, fast, recommended)',
                'llama3.1' => 'Llama 3.1 (8B, better quality)',
                'mistral' => 'Mistral 7B (good balance)',
                'phi3' => 'Phi-3 Mini (3.8B, Microsoft)',
                'qwen2.5' => 'Qwen 2.5 (multilingual)',
                'gemma2' => 'Gemma 2 (Google, 9B)',
                'llava' => 'LLaVA (vision, general purpose)',
                'llava-llama3' => 'LLaVA-Llama3 (vision)',
                'llava-mistral' => 'LLaVA-Mistral (vision)',
                'bakllava' => 'BakLLaVA (vision)',
                'nomic-embed-text' => 'Nomic Embed Text (embeddings)',
                'mxbai-embed-large' => 'MXBAI Embed Large (embeddings)',
            ],
            'openai' => [
                'gpt-4o-mini' => 'GPT-4o Mini (fast, cheap, vision)',
                'gpt-4o' => 'GPT-4o (best quality, vision)',
                'gpt-3.5-turbo' => 'GPT-3.5 Turbo (legacy)',
            ],
            'anthropic' => [
                'claude-3-haiku' => 'Claude 3 Haiku (fast)',
                'claude-3-sonnet' => 'Claude 3 Sonnet (balanced)',
                'claude-3-opus' => 'Claude 3 Opus (best)',
            ],
            default => [],
        };
    }

    /**
     * Get available vision models for the provider.
     */
    private function getAvailableVisionModels(string $provider): array
    {
        return match ($provider) {
            'ollama' => [
                'llava' => 'LLaVA (general purpose, recommended)',
                'llava-llama3' => 'LLaVA-Llama3',
                'llava-mistral' => 'LLaVA-Mistral',
                'bakllava' => 'BakLLaVA',
            ],
            'openai' => [
                'gpt-4o-mini' => 'GPT-4o Mini (vision)',
                'gpt-4o' => 'GPT-4o (vision)',
            ],
            'anthropic' => [
                'claude-3-haiku' => 'Claude 3 Haiku (vision)',
                'claude-3-sonnet' => 'Claude 3 Sonnet (vision)',
                'claude-3-opus' => 'Claude 3 Opus (vision)',
            ],
            default => [],
        };
    }

    /**
     * Update .env file with new AI settings.
     */
    private function updateEnvFile(array $settings): void
    {
        $envPath = base_path('.env');
        $content = file_exists($envPath) ? file_get_contents($envPath) : '';

        $mapping = [
            'provider' => 'AI_PROVIDER',
            'base_url' => 'AI_BASE_URL',
            'model' => 'AI_MODEL',
            'vision_model' => 'AI_VISION_MODEL',
            'embedding_model' => 'AI_EMBEDDING_MODEL',
            'api_key' => 'AI_API_KEY',
        ];

        foreach ($mapping as $key => $envKey) {
            $value = $settings[$key] ?? '';
            $pattern = "/^{$envKey}=.*/m";
            $replacement = "{$envKey}=".($value !== '' ? $value : '');

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $replacement, $content);
            } else {
                $content .= "\n{$replacement}";
            }
        }

        // Handle temperature and max_tokens in config file instead
        // (they're runtime options, not env vars)

        file_put_contents($envPath, trim($content)."\n");
    }
}
