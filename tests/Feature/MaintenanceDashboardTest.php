<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Damage;
use App\Models\Equipment;
use App\Models\Inspection;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => UserRole::ADMIN, 'role_setup_completed' => true, 'email_verified_at' => now()]);
    }

    public function test_endpoints_return_exact_aggregated_data(): void
    {
        $owner = User::factory()->create(['role' => UserRole::OWNER]);
        $categoryId = \Illuminate\Support\Facades\DB::table('categories')->insertGetId(['name' => 'Batteries']);
        $equipment = Equipment::query()->create(['name' => 'Battery', 'owner_id' => $owner->id, 'category_id' => $categoryId]);

        Maintenance::factory()->create(['equipment_id' => $equipment->id, 'start_date' => '2026-01-10', 'cost' => 100]);
        Maintenance::factory()->create(['equipment_id' => $equipment->id, 'start_date' => '2026-01-20', 'cost' => 50.50]);
        Maintenance::factory()->create(['equipment_id' => $equipment->id, 'start_date' => '2026-02-01', 'cost' => 75]);

        $inspection = Inspection::factory()->create(['equipment_id' => $equipment->id]);
        foreach (['2026-01-01', '2026-01-11', '2026-01-31'] as $date) {
            $damage = Damage::query()->create([
                'inspection_id' => $inspection->id,
                'equipment_id' => $equipment->id,
                'severity' => 'severe',
                'description' => 'Failure',
                'status' => 'reported',
            ]);
            $damage->forceFill(['created_at' => $date, 'updated_at' => $date])->saveQuietly();
        }

        $this->actingAs($this->admin)->getJson(route('admin.technical.dashboard.costs'))
            ->assertOk()->assertExactJson(['labels' => ['2026-01', '2026-02'], 'datasets' => [['label' => 'Maintenance costs', 'data' => [150.5, 75]]]]);
        $this->actingAs($this->admin)->getJson(route('admin.technical.dashboard.failures'))
            ->assertOk()->assertExactJson(['labels' => ['Batteries'], 'datasets' => [['label' => 'Failures', 'data' => [3]]]]);
        $this->actingAs($this->admin)->getJson(route('admin.technical.dashboard.intervals'))
            ->assertOk()->assertExactJson(['labels' => ['Batteries'], 'datasets' => [['label' => 'Average days between failures', 'data' => [15]]]]);
    }

    public function test_endpoints_return_empty_chart_structure_without_data(): void
    {
        foreach (['costs', 'failures', 'intervals'] as $endpoint) {
            $this->actingAs($this->admin)->getJson(route("admin.technical.dashboard.{$endpoint}"))
                ->assertOk()->assertJsonPath('labels', [])->assertJsonPath('datasets.0.data', []);
        }
    }

    public function test_dashboard_is_restricted_to_administrators(): void
    {
        $owner = User::factory()->create(['role' => UserRole::OWNER, 'role_setup_completed' => true, 'email_verified_at' => now()]);

        $this->actingAs($owner)->get(route('admin.technical.dashboard'))->assertForbidden();
        $this->actingAs($owner)->getJson(route('admin.technical.dashboard.costs'))->assertForbidden();
    }
}


