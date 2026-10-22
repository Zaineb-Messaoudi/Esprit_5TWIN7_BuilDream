<?php

namespace App\Http\Controllers;

use App\Models\AiGeneration;
use App\Models\AiStudioSetting;
use App\Services\CatalogAssistantContext;
use App\Services\GeminiStudioService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class AiDemoController extends Controller
{
    private const TOOLS = [
        'text' => [
            'title' => 'Text generator',
            'description' => 'Generate finished writing for product listings, messages, and campaign content.',
            'kind' => 'text',
        ],
        'code' => [
            'title' => 'Code generator',
            'description' => 'Generate secure, maintainable code for a specific task.',
            'kind' => 'code',
        ],
        'chat' => [
            'title' => 'AI chat',
            'description' => 'Ask follow-up questions in a conversational SolarShare assistant.',
            'kind' => 'chat',
        ],
        'history' => [
            'title' => 'Generation history',
            'description' => 'Review, download, or delete the AI generations saved to your account.',
            'kind' => 'history',
        ],
        'settings' => [
            'title' => 'AI settings',
            'description' => 'Set your preferred text model and generation limits.',
            'kind' => 'settings',
        ],
    ];

    public function index(): View
    {
        $user = auth()->user();

        if (! $user?->exists) {
            return view('pages.dashboard.ai', [
                'title' => 'AI Dashboard', 'totalRequests' => 0, 'totalInputTokens' => 0,
                'totalOutputTokens' => 0, 'successRate' => 0, 'recentGenerations' => collect(),
                'configured' => app(GeminiStudioService::class)->configured(),
            ]);
        }

        $records = $this->recordsFor($user);

        return view('pages.dashboard.ai', [
            'title' => 'AI Dashboard',
            'totalRequests' => (clone $records)->where('status', 'succeeded')->count(),
            'totalInputTokens' => (clone $records)->sum('input_tokens'),
            'totalOutputTokens' => (clone $records)->sum('output_tokens'),
            'successRate' => $this->successRate($records),
            'recentGenerations' => (clone $records)->latest()->limit(6)->get(),
            'configured' => app(GeminiStudioService::class)->configured(),
            'capabilities' => app(GeminiStudioService::class)->capabilities(),
        ]);
    }

    public function usage(): View
    {
        $user = auth()->user();

        if (! $user?->exists) {
            return view('pages.ai.usage', [
                'title' => 'AI Usage', 'totalRequests' => 0, 'failedRequests' => 0,
                'inputTokens' => 0, 'outputTokens' => 0, 'recentGenerations' => collect(),
                'configured' => app(GeminiStudioService::class)->configured(),
            ]);
        }

        $records = $this->recordsFor($user);

        return view('pages.ai.usage', [
            'title' => 'AI Usage',
            'totalRequests' => (clone $records)->where('status', 'succeeded')->count(),
            'failedRequests' => (clone $records)->where('status', 'failed')->count(),
            'inputTokens' => (clone $records)->sum('input_tokens'),
            'outputTokens' => (clone $records)->sum('output_tokens'),
            'recentGenerations' => (clone $records)->latest()->limit(20)->get(),
            'configured' => app(GeminiStudioService::class)->configured(),
        ]);
    }

    public function tool(Request $request, string $tool): View
    {
        abort_unless(isset(self::TOOLS[$tool]), 404);

        $user = auth()->user();

        return view('pages.ai.studio', [
            'title' => self::TOOLS[$tool]['title'],
            'tool' => self::TOOLS[$tool],
            'tools' => self::TOOLS,
            'settings' => $this->settingsFor($user),
            'initialPrompt' => Str::limit((string) $request->query('prompt', ''), 1200, ''),
            'recentGenerations' => $tool === 'history'
                ? ($user?->exists ? $this->recordsFor($user)->latest()->limit(50)->get() : collect())
                : collect(),
            'configured' => app(GeminiStudioService::class)->configured(),
        ]);
    }

    public function generate(Request $request, GeminiStudioService $gemini, CatalogAssistantContext $catalogContext): JsonResponse
    {
        $data = $request->validate([
            'tool' => ['required', 'in:text,code,chat'],
            'prompt' => ['required', 'string', 'min:1', 'max:1200'],
            'history' => ['sometimes', 'array', 'max:20'],
            'history.*.role' => ['required_with:history', 'in:user,assistant'],
            'history.*.text' => ['required_with:history', 'string', 'max:2000'],
        ]);

        $user = $request->user();
        $settings = $this->settingsFor($user);

        if ($data['tool'] === 'chat') {
            $settings['catalog_context'] = $catalogContext->forConversation(
                trim($data['prompt']),
                $data['history'] ?? []
            );
        }

        try {
            $result = $gemini->generate($data['tool'], trim($data['prompt']), $data['history'] ?? [], $settings);
            $mediaPath = null;

            if (is_string($result['media'] ?? null)) {
                $extension = match ($result['mime_type'] ?? null) {
                    'image/jpeg' => 'jpg',
                    'image/webp' => 'webp',
                    default => 'bin',
                };
                $mediaPath = 'ai-generations/'.$user->id.'/'.Str::uuid().'.'.$extension;
                Storage::disk('local')->put($mediaPath, $result['media']);
            }

            $generation = AiGeneration::create([
                'user_id' => $user->id,
                'tool' => $data['tool'],
                'prompt' => trim($data['prompt']),
                'result_text' => $result['text'] ?: null,
                'media_path' => $mediaPath,
                'media_mime_type' => $result['mime_type'],
                'model' => $result['model'],
                'status' => 'succeeded',
                'input_tokens' => $result['input_tokens'],
                'output_tokens' => $result['output_tokens'],
            ]);

            return response()->json([
                'id' => $generation->id,
                'text' => $generation->result_text,
                'media_url' => $generation->media_path ? route('ai.generations.media', $generation) : null,
                'model' => $generation->model,
                'input_tokens' => $generation->input_tokens,
                'output_tokens' => $generation->output_tokens,
            ]);
        } catch (ConnectionException $exception) {
            $message = __('The AI provider could not be reached. Please wait a moment and try again.');
            Log::warning('AI Studio provider connection failed.', [
                'tool' => $data['tool'],
                'exception' => class_basename($exception),
            ]);

            AiGeneration::create([
                'user_id' => $user->id,
                'tool' => $data['tool'],
                'prompt' => trim($data['prompt']),
                'model' => $this->modelFor($data['tool'], $settings),
                'status' => 'failed',
                'error_message' => $message,
            ]);

            return response()->json(['message' => $message], 503);
        } catch (RuntimeException $exception) {
            AiGeneration::create([
                'user_id' => $user->id,
                'tool' => $data['tool'],
                'prompt' => trim($data['prompt']),
                'model' => $this->modelFor($data['tool'], $settings),
                'status' => 'failed',
                'error_message' => Str::limit($exception->getMessage(), 240),
            ]);

            return response()->json(['message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::warning('AI Studio generation failed.', ['tool' => $data['tool'], 'exception' => class_basename($exception)]);
            AiGeneration::create([
                'user_id' => $user->id,
                'tool' => $data['tool'],
                'prompt' => trim($data['prompt']),
                'model' => $this->modelFor($data['tool'], $settings),
                'status' => 'failed',
                'error_message' => __('The AI request could not be completed. Please try again.'),
            ]);

            return response()->json(['message' => __('The AI request could not be completed. Please try again.')], 502);
        }
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'text_model' => ['required', 'in:openai/gpt-oss-20b'],
            'max_output_tokens' => ['required', 'integer', 'between:256,8192'],
            'temperature' => ['required', 'numeric', 'between:0,1'],
        ]);

        AiStudioSetting::updateOrCreate(['user_id' => $request->user()->id], $data);

        return redirect()->route('ai.settings')->with('status', 'ai-settings-saved');
    }

    public function media(Request $request, AiGeneration $generation)
    {
        abort_unless($generation->media_path && $this->canView($request, $generation), 404);

        return Storage::disk('local')->response($generation->media_path, basename($generation->media_path), [
            'Content-Type' => $generation->media_mime_type ?? 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(Request $request, AiGeneration $generation): RedirectResponse
    {
        abort_unless($this->canView($request, $generation), 404);

        if ($generation->media_path) {
            Storage::disk('local')->delete($generation->media_path);
        }

        $generation->delete();

        return back()->with('status', 'ai-generation-deleted');
    }

    private function settingsFor($user): array
    {
        if (! $user?->exists) {
            return ['text_model' => config('ai.groq.model'), 'max_output_tokens' => 2048, 'temperature' => 0.4];
        }

        $settings = AiStudioSetting::firstOrCreate(['user_id' => $user->id]);

        return [
            'text_model' => $settings->text_model === 'openai/gpt-oss-20b'
                ? $settings->text_model
                : (string) config('ai.groq.model', 'openai/gpt-oss-20b'),
            'max_output_tokens' => $settings->max_output_tokens ?: 2048,
            'temperature' => (float) ($settings->temperature ?? 0.4),
        ];
    }

    private function recordsFor($user)
    {
        $query = AiGeneration::query();

        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    private function successRate($records): int
    {
        $total = (clone $records)->count();

        return $total === 0 ? 0 : (int) round((clone $records)->where('status', 'succeeded')->count() / $total * 100);
    }

    private function canView(Request $request, AiGeneration $generation): bool
    {
        return $request->user()->isAdmin() || $generation->user_id === $request->user()->id;
    }

    private function modelFor(string $tool, array $settings): string
    {
        return match ($tool) {
            'chat' => (string) config('ai.groq.model'),
            default => $settings['text_model'] ?: (string) config('ai.groq.model', 'openai/gpt-oss-20b'),
        };
    }
}
