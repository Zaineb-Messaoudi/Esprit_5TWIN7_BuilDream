<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Equipment;
use App\Models\Inspection;
use App\Models\Maintenance;
use App\Models\MaintenanceReport;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TechnicalMysqlCrudTest extends TestCase
{
    use DatabaseTransactions;

    private function ownerWithEquipment(): array
    {
        $equipment = Equipment::query()->first();
        $this->assertNotNull($equipment, 'Run TechnicalPreviewSeeder to provide equipment for this MySQL CRUD test.');

        $owner = User::query()->findOrFail($equipment->owner_id);
        $this->assertSame(UserRole::OWNER, $owner->role);

        return [$owner, $equipment];
    }

    private function requireMysql(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            $this->markTestSkipped('Run this test with DB_CONNECTION=mysql and DB_DATABASE set to the local SolarShare database.');
        }
    }

    public function test_owner_can_read_create_update_and_delete_each_technical_record_in_mysql(): void
    {
        $this->requireMysql();
        [$owner, $equipment] = $this->ownerWithEquipment();
        $this->actingAs($owner);

        foreach (['maintenances', 'reports', 'inspections'] as $resource) {
            $this->get(route("technical.$resource.index"))->assertOk()->assertDontSee('No records yet.');
            $this->get(route("technical.$resource.create"))->assertOk();
        }

        $maintenanceData = [
            'equipment_id' => $equipment->id,
            'start_date' => '2026-10-06',
            'end_date' => '2026-10-07',
            'reason' => 'MySQL CRUD test',
            'cost' => '42.50',
            'status' => 'planned',
            'notes' => 'Created in a rolled-back transaction',
        ];
        $this->post(route('technical.maintenances.store'), $maintenanceData)->assertSessionHasNoErrors();
        $maintenance = Maintenance::query()->where('reason', 'MySQL CRUD test')->firstOrFail();
        $this->assertDatabaseHas('maintenances', ['id' => $maintenance->id, 'equipment_id' => $equipment->id]);
        $this->get(route('technical.maintenances.show', $maintenance))->assertOk()->assertSee('MySQL CRUD test');
        $this->get(route('technical.maintenances.edit', $maintenance))->assertOk();
        $this->put(route('technical.maintenances.update', $maintenance), array_replace($maintenanceData, [
            'status' => 'completed', 'cost' => '52.75',
        ]))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('maintenances', ['id' => $maintenance->id, 'status' => 'completed', 'cost' => '52.75']);

        $reportData = [
            'maintenance_id' => $maintenance->id,
            'diagnosis' => 'MySQL test diagnosis',
            'actions_taken' => 'Checked connections',
            'parts_replaced' => 'Fuse',
            'technician_notes' => 'Working normally',
            'report_date' => '2026-10-07',
        ];
        $this->post(route('technical.reports.store'), $reportData)->assertSessionHasNoErrors();
        $report = MaintenanceReport::query()->where('maintenance_id', $maintenance->id)->firstOrFail();
        $this->assertDatabaseHas('maintenance_reports', ['id' => $report->id, 'maintenance_id' => $maintenance->id]);
        $this->get(route('technical.reports.show', $report))->assertOk()->assertSee('MySQL test diagnosis');
        $this->get(route('technical.reports.edit', $report))->assertOk();
        $this->put(route('technical.reports.update', $report), array_replace($reportData, [
            'diagnosis' => 'Updated MySQL diagnosis',
        ]))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('maintenance_reports', ['id' => $report->id, 'diagnosis' => 'Updated MySQL diagnosis']);

        $inspectionData = [
            'equipment_id' => $equipment->id,
            'rental_id' => null,
            'inspection_date' => '2026-10-08',
            'condition_before' => 'Clean',
            'condition_after' => 'Scuffed',
            'damage_detected' => '1',
            'comments' => 'MySQL inspection test',
        ];
        $this->post(route('technical.inspections.store'), $inspectionData)->assertSessionHasNoErrors();
        $inspection = Inspection::query()->where('comments', 'MySQL inspection test')->firstOrFail();
        $this->assertDatabaseHas('inspections', ['id' => $inspection->id, 'damage_detected' => 1]);
        $this->get(route('technical.inspections.show', $inspection))->assertOk()->assertSee('MySQL inspection test');
        $this->get(route('technical.inspections.edit', $inspection))->assertOk();
        $this->put(route('technical.inspections.update', $inspection), array_replace($inspectionData, [
            'damage_detected' => '0', 'comments' => 'Updated MySQL inspection',
        ]))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('inspections', ['id' => $inspection->id, 'damage_detected' => 0]);

        $this->delete(route('technical.reports.destroy', $report))->assertRedirect(route('technical.reports.index'));
        $this->assertDatabaseMissing('maintenance_reports', ['id' => $report->id]);
        $this->delete(route('technical.inspections.destroy', $inspection))->assertRedirect(route('technical.inspections.index'));
        $this->assertDatabaseMissing('inspections', ['id' => $inspection->id]);
        $this->delete(route('technical.maintenances.destroy', $maintenance))->assertRedirect(route('technical.maintenances.index'));
        $this->assertDatabaseMissing('maintenances', ['id' => $maintenance->id]);
    }

    public function test_mysql_forms_validate_input_and_owner_cannot_use_another_owners_equipment(): void
    {
        $this->requireMysql();
        [$owner, $equipment] = $this->ownerWithEquipment();
        $this->actingAs($owner);

        $this->post(route('technical.maintenances.store'), [
            'equipment_id' => $equipment->id,
            'start_date' => '2026-10-08',
            'end_date' => '2026-10-07',
            'reason' => '',
            'cost' => -1,
            'status' => 'invalid',
        ])->assertSessionHasErrors(['end_date', 'reason', 'cost', 'status']);

        $this->post(route('technical.reports.store'), [
            'maintenance_id' => 999999999,
            'diagnosis' => '',
            'actions_taken' => '',
            'report_date' => 'bad-date',
        ])->assertSessionHasErrors(['maintenance_id', 'diagnosis', 'actions_taken', 'report_date']);

        $this->post(route('technical.inspections.store'), [
            'equipment_id' => $equipment->id,
            'rental_id' => null,
            'inspection_date' => 'bad-date',
            'condition_before' => '',
            'condition_after' => '',
            'damage_detected' => 'not-a-boolean',
        ])->assertSessionHasErrors(['inspection_date', 'condition_before', 'condition_after', 'damage_detected']);

        $otherEquipment = Equipment::query()->where('owner_id', '!=', $owner->id)->first();
        if ($otherEquipment) {
            $this->post(route('technical.maintenances.store'), [
                'equipment_id' => $otherEquipment->id,
                'start_date' => '2026-10-08',
                'reason' => 'Must be refused',
                'cost' => 1,
                'status' => 'planned',
            ])->assertSessionHasErrors('equipment_id');
        }
    }

    public function test_admin_can_open_all_back_office_technical_pages_in_mysql(): void
    {
        $this->requireMysql();
        $admin = User::query()->where('role', UserRole::ADMIN->value)->first();
        if (! $admin) {
            $admin = User::factory()->create([
                'role' => UserRole::ADMIN,
                'role_setup_completed' => true,
                'email_verified_at' => now(),
            ]);
        }
        $this->actingAs($admin);

        foreach (['maintenances' => Maintenance::class, 'reports' => MaintenanceReport::class, 'inspections' => Inspection::class] as $resource => $model) {
            $record = $model::query()->firstOrFail();
            $this->get(route("admin.technical.$resource.index"))->assertOk()->assertDontSee('No records yet.');
            $this->get(route("admin.technical.$resource.create"))->assertOk();
            $this->get(route("admin.technical.$resource.show", $record))->assertOk();
            $this->get(route("admin.technical.$resource.edit", $record))->assertOk();
        }
    }
}
