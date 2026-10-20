<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Damage;
use App\Models\Equipment;
use App\Models\InspectionChecklist;
use App\Models\InspectionChecklistItem;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InspectionChecklistFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('equipment', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function test_owner_can_submit_checklist_and_critical_failure_creates_damage(): void
    {
        $owner = User::factory()->create(['role' => UserRole::OWNER, 'role_setup_completed' => true, 'email_verified_at' => now()]);
        $equipment = Equipment::query()->create(['name' => 'Battery', 'owner_id' => $owner->id]);
        $checklist = InspectionChecklist::factory()->create();
        $item = InspectionChecklistItem::factory()->create(['inspection_checklist_id' => $checklist->id, 'is_critical' => true]);

        $response = $this->actingAs($owner)->post(route('technical.inspections.store'), [
            'equipment_id' => $equipment->id,
            'inspection_checklist_id' => $checklist->id,
            'inspection_date' => '2026-10-20',
            'condition_before' => 'Good',
            'condition_after' => 'Damaged',
            'damage_detected' => '0',
            'checklist_results' => [['item_id' => $item->id, 'passed' => '0']],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame(1, Damage::query()->count());
        $this->assertDatabaseHas('inspections', ['equipment_id' => $equipment->id, 'damage_detected' => true]);
    }

    public function test_checklist_input_rejects_missing_and_foreign_items(): void
    {
        $owner = User::factory()->create(['role' => UserRole::OWNER, 'role_setup_completed' => true, 'email_verified_at' => now()]);
        $equipment = Equipment::query()->create(['name' => 'Panel', 'owner_id' => $owner->id]);
        $checklist = InspectionChecklist::factory()->create();
        InspectionChecklistItem::factory()->create(['inspection_checklist_id' => $checklist->id]);
        $foreignItem = InspectionChecklistItem::factory()->create();

        $this->actingAs($owner)->post(route('technical.inspections.store'), [
            'equipment_id' => $equipment->id,
            'inspection_checklist_id' => $checklist->id,
            'inspection_date' => '2026-10-20',
            'condition_before' => 'Good',
            'condition_after' => 'Good',
            'damage_detected' => '0',
            'checklist_results' => [['item_id' => $foreignItem->id, 'passed' => 'invalid']],
        ])->assertSessionHasErrors(['checklist_results', 'checklist_results.0.passed']);
    }
}
