<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');

test('natural language search endpoint requires authentication', function () {
    $this->postJson(route('ai.natural-language-search'), ['q' => 'battery for camping'])
        ->assertUnauthorized();
});

test('generate description endpoint requires authentication', function () {
    $this->postJson(route('ai.generate-description'), [
        'name' => 'Test Battery',
        'brand' => 'TestBrand',
        'model' => 'TestModel',
        'category' => 'Battery',
    ])->assertUnauthorized();
});

test('suggest price endpoint requires authentication', function () {
    $this->postJson(route('ai.suggest-price'), [
        'category' => 'Battery',
    ])->assertUnauthorized();
});

test('auto categorize endpoint requires authentication', function () {
    $this->postJson(route('ai.auto-categorize'), [
        'name' => 'Test Battery',
        'description' => 'A portable battery',
    ])->assertUnauthorized();
});

test('chatbot endpoint requires authentication', function () {
    $this->postJson(route('ai.chatbot'), [
        'message' => 'What is the capacity?',
        'context_equipment_ids' => [1],
    ])->assertUnauthorized();
});

test('semantic search endpoint requires authentication', function () {
    $this->postJson(route('ai.semantic-search'), ['q' => 'battery'])
        ->assertUnauthorized();
});

test('validate image endpoint requires authentication', function () {
    $this->postJson(route('ai.validate-image'), [
        'equipment_name' => 'Test Battery',
        'category' => 'Battery',
    ])->assertUnauthorized();
});

test('catalog page loads with ai search ui', function () {
    $this->get(route('front.catalog'))
        ->assertOk()
        ->assertSee('AI Search (Natural Language)')
        ->assertSee('Semantic Search (AI)');
});

test('equipment detail page loads with chatbot ui', function () {
    $batteryCategory = Category::factory()->create(['name' => 'Battery', 'status' => 'active']);
    $battery = Equipment::factory()->create([
        'name' => 'Portable Power Station 1000Wh',
        'brand' => 'EcoFlow',
        'model' => 'River 2 Pro',
        'category_id' => $batteryCategory->id,
        'price_per_day' => 15,
        'condition' => 'good',
        'location' => 'Tunis',
        'status' => 'available',
        'approval_status' => 'published',
    ]);
    $battery->energyProfile()->create([
        'power_watts' => 600,
        'capacity_wh' => 1000,
        'technology' => 'LiFePO4',
    ]);

    $this->get(route('front.equipment.show', $battery->id))
        ->assertOk()
        ->assertSee('Ask about this equipment')
        ->assertSee('What is the capacity and power output?');
});

test('owner equipment form loads with ai features', function () {
    $owner = User::factory()->create([
        'role' => UserRole::OWNER,
        'role_setup_completed' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($owner)
        ->get(route('front.my-publish'))
        ->assertOk()
        ->assertSee('Generate with AI')
        ->assertSee('Suggest Price')
        ->assertSee('Suggest Category')
        ->assertSee('Validate with AI');
});

test('admin ai settings page loads', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN, 'email_verified_at' => now()]);

    $this->actingAs($admin)
        ->get(route('admin.ai-settings.index'))
        ->assertOk()
        ->assertSee('AI / LLM Configuration')
        ->assertSee('Ollama (Local, FREE)')
        ->assertSee('OpenAI (Cloud, Paid)')
        ->assertSee('Anthropic (Cloud, Paid)');
});

test('ai settings can test connection', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN, 'email_verified_at' => now()]);

    $this->actingAs($admin)
        ->postJson(route('admin.ai-settings.test'))
        ->assertStatus(200)
        ->assertJsonStructure(['available']);
});

test('async ai request dispatches job and returns job_id', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    
    $this->actingAs($user)
        ->postJson(route('ai.natural-language-search'), [
            'q' => 'battery for camping',
            'async' => true,
        ])
        ->assertStatus(202)
        ->assertJsonStructure(['job_id', 'status', 'message', 'callback_secret']);
});

test('ai feedback endpoint records thumbs up/down', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $requestId = (string) Str::uuid();
    $log = \App\Models\AiUsageLog::create([
        'endpoint' => 'chatbot',
        'provider' => 'ollama',
        'model' => 'llama3',
        'user_id' => $user->id,
        'request_id' => $requestId,
        'latency_ms' => 100,
        'tokens_input' => 50,
        'tokens_output' => 100,
        'tokens_total' => 150,
        'success' => true,
    ]);

    $this->actingAs($user)
        ->postJson(route('ai.feedback'), [
            'request_id' => $requestId,
            'rating' => 1,
            'feedback' => 'Great answer!',
        ])
        ->assertOk()
        ->assertJson(['success' => true, 'message' => 'Feedback recorded']);

    $log->refresh();
    $this->assertEquals(1, $log->quality_rating);
    $this->assertEquals('Great answer!', $log->quality_feedback);
});

test('ai feedback rejects invalid rating', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $requestId = (string) Str::uuid();

    $this->actingAs($user)
        ->postJson(route('ai.feedback'), [
            'request_id' => $requestId,
            'rating' => 5,
        ])
        ->assertStatus(422);
});

test('ai feedback returns 404 for non-existent request', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $this->actingAs($user)
        ->postJson(route('ai.feedback'), [
            'request_id' => (string) Str::uuid(),
            'rating' => 1,
        ])
        ->assertNotFound();
});