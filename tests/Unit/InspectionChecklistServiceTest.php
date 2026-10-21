<?php

namespace Tests\Unit;

use App\Models\Damage;
use App\Models\Equipment;
use App\Models\Inspection;
use App\Models\InspectionChecklist;
use App\Models\InspectionChecklistItem;
use App\Models\User;
use App\Services\Maintenance\InspectionChecklistService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InspectionChecklistServiceTest extends TestCase
{
    use RefreshDatabase;

    private InspectionChecklistService $service;

    private Equipment $equipment;

    private InspectionChecklist $checklist;

    private InspectionChecklistItem $criticalItem;

    private InspectionChecklistItem $regularItem;

    protected function setUp(): void
    {
        parent::setUp();

        $owner = User::factory()->create();
        $this->equipment = Equipment::factory()->create(['name' => 'Test battery', 'owner_id' => $owner->id]);
        $this->checklist = InspectionChecklist::factory()->create();
        $this->criticalItem = InspectionChecklistItem::factory()->create([
            'inspection_checklist_id' => $this->checklist->id,
            'label' => 'Battery casing is not swollen',
            'is_critical' => true,
            'sort_order' => 1,
        ]);
        $this->regularItem = InspectionChecklistItem::factory()->create([
            'inspection_checklist_id' => $this->checklist->id,
            'label' => 'Accessories are complete',
            'is_critical' => false,
            'sort_order' => 2,
        ]);
        $this->service = app(InspectionChecklistService::class);
    }

    public function test_results_are_saved_as_json_and_critical_failure_creates_damage(): void
    {
        $inspection = $this->service->create($this->payload(false, true));

        $this->assertFalse($inspection->checklist_results[0]['passed']);
        $this->assertTrue($inspection->checklist_results[0]['is_critical']);
        $this->assertTrue($inspection->fresh()->damage_detected);
        $this->assertDatabaseHas('damages', [
            'inspection_id' => $inspection->id,
            'inspection_checklist_item_id' => $this->criticalItem->id,
            'severity' => 'severe',
        ]);
    }

    public function test_non_critical_failure_does_not_create_damage(): void
    {
        $inspection = $this->service->create($this->payload(true, false));

        $this->assertFalse($inspection->damage_detected);
        $this->assertSame(0, Damage::query()->where('inspection_id', $inspection->id)->count());
    }

    public function test_transaction_rolls_back_when_results_are_incomplete(): void
    {
        $before = Inspection::query()->count();

        try {
            $this->service->create(array_replace($this->payload(true, true), [
                'checklist_results' => [['item_id' => $this->criticalItem->id, 'passed' => true]],
            ]));
            $this->fail('Expected incomplete checklist validation to fail.');
        } catch (ValidationException) {
            $this->assertSame($before, Inspection::query()->count());
            $this->assertSame(0, Damage::query()->count());
        }
    }

    private function payload(bool $criticalPassed, bool $regularPassed): array
    {
        return [
            'equipment_id' => $this->equipment->id,
            'rental_id' => null,
            'inspection_checklist_id' => $this->checklist->id,
            'inspection_date' => '2026-10-20',
            'condition_before' => 'Good',
            'condition_after' => 'Good',
            'damage_detected' => false,
            'checklist_results' => [
                ['item_id' => $this->criticalItem->id, 'passed' => $criticalPassed],
                ['item_id' => $this->regularItem->id, 'passed' => $regularPassed],
            ],
            'comments' => null,
        ];
    }
}
