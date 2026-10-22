<?php

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Support\Facades\Http;

function aiStudioUser(): User
{
    return User::factory()->create([
        'role' => UserRole::BUYER,
        'role_setup_completed' => true,
        'email_verified_at' => now(),
    ]);
}

function geminiTextResponse(string $text = 'Generated safely.'): array
{
    return [
        'output_text' => $text,
        'usage' => ['input_tokens' => 8, 'output_tokens' => 5],
    ];
}

function configureGeminiStudioForTest(): void
{
    config([
        'ai.provider' => 'gemini', 'ai.studio.enabled' => true, 'ai.gemini.key' => 'test-gemini-key',
        'ai.studio.chat_model' => 'gemini-3.8-flash', 'ai.studio.text_model' => 'gemini-3.8-flash',
    ]);
}

it('generates text and code with Gemini and records usage privately', function () {
    configureGeminiStudioForTest();
    Http::fake(['*' => Http::response(geminiTextResponse(), 200)]);
    $user = aiStudioUser();

    $response = $this->actingAs($user)->postJson(route('ai.generate'), [
        'tool' => 'text', 'prompt' => 'Write a short product introduction.',
    ]);

    $response->assertOk()->assertJsonPath('text', 'Generated safely.');
    $this->assertDatabaseHas('ai_generations', [
        'user_id' => $user->id, 'tool' => 'text', 'status' => 'succeeded',
        'input_tokens' => 8, 'output_tokens' => 5,
    ]);

    $this->actingAs($user)->postJson(route('ai.generate'), [
        'tool' => 'code', 'prompt' => 'Create a safe input validator.',
    ])->assertOk()->assertJsonPath('text', 'Generated safely.');
});

it('keeps multi-turn chat context and saves user preferences', function () {
    configureGeminiStudioForTest();
    Http::fake(['*' => Http::response([
        'candidates' => [['content' => ['parts' => [['text' => 'Hello again.']]]]],
        'usageMetadata' => ['promptTokenCount' => 8, 'candidatesTokenCount' => 5],
    ], 200)]);
    $user = aiStudioUser();

    $this->actingAs($user)->postJson(route('ai.generate'), [
        'tool' => 'chat',
        'prompt' => 'What about the battery?',
        'history' => [
            ['role' => 'user', 'text' => 'Can I use solar power?'],
            ['role' => 'assistant', 'text' => 'Yes, with suitable equipment.'],
        ],
    ])->assertOk()->assertJsonPath('text', 'Hello again.');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/models/gemini-3.5-flash-lite:generateContent')
        && $request['contents'][0]['parts'][0]['text'] === 'Can I use solar power?'
        && $request['contents'][1]['parts'][0]['text'] === 'Yes, with suitable equipment.'
        && str_contains($request['contents'][2]['parts'][0]['text'], 'What about the battery?'));

    $this->from(route('ai.settings'))->post(route('ai.settings.update'), [
        'text_model' => 'openai/gpt-oss-20b', 'max_output_tokens' => 4096, 'temperature' => 0.7,
    ])->assertRedirect(route('ai.settings'));
    $this->assertDatabaseHas('ai_studio_settings', [
        'user_id' => $user->id, 'text_model' => 'openai/gpt-oss-20b', 'max_output_tokens' => 4096,
    ]);

    $this->actingAs($user)->get(route('ai.history'))->assertOk()->assertSee('What about the battery?');
    $this->actingAs($user)->get(route('ai.usage'))->assertOk()->assertSee('Successful requests');
    $this->actingAs($user)->get(route('ai.index'))->assertOk()->assertSee('AI Studio tools');
});

it('falls back to another Gemini model when Flash-Lite chat is rate limited or overloaded', function (int $status) {
    configureGeminiStudioForTest();
    $sequence = Http::sequence()
        ->push(['error' => ['message' => 'Temporarily unavailable.']], $status);

    if ($status === 429) {
        $sequence->push([
            'candidates' => [['content' => ['parts' => [['text' => 'Fallback answer.']]]]],
            'usageMetadata' => ['promptTokenCount' => 8, 'candidatesTokenCount' => 5],
        ], 200);
    } else {
        $sequence->push(['error' => ['message' => 'Still overloaded.']], 503)
            ->push(geminiTextResponse('Fallback answer.'), 200);
    }

    Http::fake(['*' => $sequence]);
    $user = aiStudioUser();

    $response = $this->actingAs($user)->postJson(route('ai.generate'), [
        'tool' => 'chat', 'prompt' => 'Help me choose a solar kit.',
    ]);

    $response->assertOk()
        ->assertJsonPath('text', 'Fallback answer.')
        ->assertJsonPath('model', $status === 429 ? 'gemini-3.6-flash' : 'gemini-3.8-flash');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/models/gemini-3.5-flash-lite:generateContent'));
    Http::assertSent(fn ($request) => str_contains($request->url(), '/models/gemini-3.6-flash:generateContent'));

    if ($status === 503) {
        Http::assertSent(fn ($request) => str_contains($request->url(), '/interactions'));
    }
})->with([429, 503]);

it('uses Groq for the AI chat and records its token usage', function () {
    config(['ai.provider' => 'groq', 'ai.studio.enabled' => true, 'ai.groq.key' => 'test-groq-key', 'ai.groq.model' => 'openai/gpt-oss-20b']);
    Http::fake(['api.groq.com/*' => Http::response([
        'choices' => [['message' => ['content' => 'Groq answer.']]],
        'usage' => ['prompt_tokens' => 11, 'completion_tokens' => 6],
    ], 200)]);
    $user = aiStudioUser();

    $this->actingAs($user)->postJson(route('ai.generate'), [
        'tool' => 'chat',
        'prompt' => 'How do I reserve a solar kit?',
        'history' => [['role' => 'assistant', 'text' => 'I can help with that.']],
    ])->assertOk()->assertJsonPath('text', 'Groq answer.')->assertJsonPath('model', 'openai/gpt-oss-20b');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/chat/completions')
        && $request->hasHeader('Authorization', 'Bearer test-groq-key')
        && data_get($request->data(), 'messages.1.content') === 'I can help with that.'
        && data_get($request->data(), 'messages.2.content') === 'How do I reserve a solar kit?');
    $this->assertDatabaseHas('ai_generations', [
        'user_id' => $user->id, 'tool' => 'chat', 'status' => 'succeeded',
        'input_tokens' => 11, 'output_tokens' => 6, 'model' => 'openai/gpt-oss-20b',
    ]);
});

it('grounds catalogue questions in published available equipment from the requested city', function () {
    config(['ai.provider' => 'groq', 'ai.studio.enabled' => true, 'ai.groq.key' => 'test-groq-key']);
    $category = Category::factory()->create(['name' => 'Solar batteries']);
    Equipment::factory()->create([
        'category_id' => $category->id,
        'name' => 'Tunis solar battery',
        'location' => 'Tunis',
        'price_per_day' => 18,
        'status' => 'available',
        'approval_status' => 'published',
    ]);
    Equipment::factory()->create([
        'name' => 'Sousse solar panel',
        'location' => 'Sousse',
        'status' => 'available',
        'approval_status' => 'published',
    ]);
    Http::fake(['api.groq.com/*' => Http::response([
        'choices' => [['message' => ['content' => 'A battery is listed in Tunis for 18 TND per day. Date availability must be checked in the reservation form.']]],
        'usage' => ['prompt_tokens' => 100, 'completion_tokens' => 22],
    ], 200)]);
    $user = aiStudioUser();

    $this->actingAs($user)->postJson(route('ai.generate'), [
        'tool' => 'chat', 'prompt' => 'Quels équipements sont disponibles à Tunis ?',
    ])->assertOk();

    Http::assertSent(fn ($request) => str_contains($request->url(), '/chat/completions')
        && str_contains(data_get($request->data(), 'messages.0.content'), 'Tunis solar battery')
        && str_contains(data_get($request->data(), 'messages.0.content'), '18')
        && str_contains(data_get($request->data(), 'messages.0.content'), 'date_availability')
        && ! str_contains(data_get($request->data(), 'messages.0.content'), 'Sousse solar panel'));
});
