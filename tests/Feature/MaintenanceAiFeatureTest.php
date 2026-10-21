<?php

namespace Tests\Feature;

use App\Contracts\AiClientInterface;
use App\Enums\UserRole;
use App\Models\User;
use App\Services\Ai\FakeAiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use Tests\TestCase;

class MaintenanceAiFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => UserRole::OWNER, 'role_setup_completed' => true, 'email_verified_at' => now()]);
    }

    public function test_fake_vision_client_prefills_damage_fields(): void
    {
        $this->app->instance(AiClientInterface::class, new FakeAiClient(
            vision: [
                'damage_detected' => true,
                'comments' => 'Fissure visible sur le boîtier.',
            ],
        ));

        $this->actingAs($this->owner)->postJson(route('technical.ai.analyze-damage'), [
            'photo' => $this->fakePng(),
        ])->assertOk()
            ->assertJsonPath('damage_detected', true)
            ->assertJsonPath('comments', 'Fissure visible sur le boîtier.')
            ->assertJsonPath('severity', 'moderate')
            ->assertJsonPath('repair_hours', 6)
            ->assertJsonPath('classification_source', 'fallback');
    }

    public function test_vision_failure_and_invalid_file_have_clear_responses(): void
    {
        $this->app->instance(AiClientInterface::class, new class extends FakeAiClient
        {
            public function analyzeImage(string $absolutePath, string $mimeType): array
            {
                throw new RuntimeException('quota');
            }
        });

        $this->actingAs($this->owner)->postJson(route('technical.ai.analyze-damage'), ['photo' => $this->fakePng()])
            ->assertStatus(503)->assertJsonStructure(['message']);
        $this->actingAs($this->owner)->postJson(route('technical.ai.analyze-damage'), [
            'photo' => UploadedFile::fake()->create('payload.txt', 2, 'text/plain'),
        ])->assertUnprocessable()->assertJsonValidationErrors('photo');
    }

    public function test_audio_transcription_uses_fake_and_validates_file(): void
    {
        $this->app->instance(AiClientInterface::class, new FakeAiClient(transcription: 'Le câble est endommagé.'));

        $this->actingAs($this->owner)->postJson(route('technical.ai.transcribe'), [
            'audio' => UploadedFile::fake()->create('note.mp3', 20, 'audio/mpeg'),
        ])->assertOk()->assertExactJson(['text' => 'Le câble est endommagé.']);

        $this->actingAs($this->owner)->postJson(route('technical.ai.transcribe'), [
            'audio' => UploadedFile::fake()->create('note.txt', 20, 'text/plain'),
        ])->assertUnprocessable()->assertJsonValidationErrors('audio');
    }

    public function test_ai_routes_require_authentication_and_authorization(): void
    {
        $this->postJson(route('technical.ai.estimate-repair'), ['severity' => 'minor'])->assertUnauthorized();
        $buyer = User::factory()->create(['role' => UserRole::BUYER]);
        $this->actingAs($buyer)->postJson(route('technical.ai.estimate-repair'), ['severity' => 'minor'])->assertForbidden();
    }

    public function test_estimation_endpoint_validates_and_returns_positive_range(): void
    {
        $this->actingAs($this->owner)->postJson(route('technical.ai.estimate-repair'), ['severity' => 'minor'])
            ->assertOk()->assertJsonPath('source', 'default')->assertJsonStructure(['average', 'median', 'minimum', 'maximum', 'sample_size']);
        $this->actingAs($this->owner)->postJson(route('technical.ai.estimate-repair'), ['severity' => 'catastrophic'])
            ->assertUnprocessable()->assertJsonValidationErrors('severity');
    }

    public function test_inspection_form_contains_photo_speech_and_repair_estimate_components(): void
    {
        $response = $this->actingAs($this->owner)->get(route('technical.inspections.create'));
        $response->assertOk()
            ->assertSee('damage_photo_analysis')
            ->assertSee('toggleDictation()')
            ->assertSee('estimateRepairCost()');
    }

    private function fakePng(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('damage.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9Zl1sAAAAASUVORK5CYII=', true));
    }
}
