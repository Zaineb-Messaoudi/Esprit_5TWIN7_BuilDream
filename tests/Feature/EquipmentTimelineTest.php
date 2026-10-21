<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Equipment;
use App\Models\Inspection;
use App\Models\Maintenance;
use App\Models\User;
use App\Services\Maintenance\EquipmentTimelineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentTimelineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_timeline_merges_sorts_and_isolates_equipment(): void
    {
        $owner = User::factory()->create(['role' => UserRole::OWNER, 'role_setup_completed' => true, 'email_verified_at' => now()]);
        $equipment = Equipment::factory()->create(['name' => 'Tracked equipment', 'owner_id' => $owner->id]);
        $other = Equipment::factory()->create(['name' => 'Other equipment', 'owner_id' => $owner->id]);
        Maintenance::factory()->create(['equipment_id' => $equipment->id, 'start_date' => '2026-01-10', 'reason' => 'Older maintenance']);
        Inspection::factory()->create(['equipment_id' => $equipment->id, 'inspection_date' => '2026-03-10', 'comments' => 'Latest inspection', 'damage_detected' => false]);
        Maintenance::factory()->create(['equipment_id' => $other->id, 'start_date' => '2026-04-10', 'reason' => 'Must stay isolated']);

        $timeline = app(EquipmentTimelineService::class)->forEquipment($equipment->id);

        $this->assertSame(['inspection', 'maintenance'], $timeline->pluck('type')->all());
        $this->assertCount(2, $timeline);
        $this->actingAs($owner)->get(route('technical.equipment.history', $equipment))
            ->assertOk()->assertSee('Inspection completed')->assertSee('Older maintenance')->assertDontSee('Must stay isolated');
    }

    public function test_empty_history_and_foreign_owner_access(): void
    {
        $owner = User::factory()->create(['role' => UserRole::OWNER, 'role_setup_completed' => true, 'email_verified_at' => now()]);
        $otherOwner = User::factory()->create(['role' => UserRole::OWNER, 'role_setup_completed' => true, 'email_verified_at' => now()]);
        $equipment = Equipment::factory()->create(['name' => 'New equipment', 'owner_id' => $owner->id]);

        $this->actingAs($owner)->get(route('technical.equipment.history', $equipment))->assertOk()->assertSee('No technical history.');
        $this->actingAs($otherOwner)->get(route('technical.equipment.history', $equipment))->assertForbidden();
    }
}

