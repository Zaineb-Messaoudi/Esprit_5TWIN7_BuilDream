<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Equipment;
use App\Models\Inspection;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TechnicalPdfTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Inspection $inspection;
    private Maintenance $maintenance;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => UserRole::OWNER, 'role_setup_completed' => true, 'email_verified_at' => now()]);
        $equipment = Equipment::query()->create(['name' => 'PDF equipment', 'owner_id' => $this->owner->id]);
        $this->inspection = Inspection::factory()->create(['equipment_id' => $equipment->id]);
        $this->maintenance = Maintenance::factory()->create(['equipment_id' => $equipment->id]);
    }

    public function test_authorized_reports_return_pdf_responses(): void
    {
        foreach ([
            route('technical.inspections.pdf', $this->inspection),
            route('technical.maintenances.pdf', $this->maintenance),
        ] as $url) {
            $this->actingAs($this->owner)->get($url)
                ->assertOk()
                ->assertHeader('content-type', 'application/pdf');
        }
    }

    public function test_non_owner_is_forbidden_and_missing_record_is_not_found(): void
    {
        $otherOwner = User::factory()->create(['role' => UserRole::OWNER, 'role_setup_completed' => true, 'email_verified_at' => now()]);

        $this->actingAs($otherOwner)->get(route('technical.inspections.pdf', $this->inspection))->assertForbidden();
        $this->actingAs($this->owner)->get('/technical/inspections/999999/pdf')->assertNotFound();
    }
}

