<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Damage;
use App\Models\Equipment;
use App\Models\Inspection;
use App\Models\Maintenance;
use App\Models\User;
use App\Notifications\AbnormalMaintenanceCostNotification;
use App\Services\Maintenance\MaintenanceCostAnomalyService;
use App\Services\Maintenance\RepairCostEstimatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdvancedAiMaintenanceServicesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_z_score_handles_outlier_small_samples_and_zero_deviation(): void
    {
        $service = new MaintenanceCostAnomalyService;

        $this->assertGreaterThan(2, $service->zScore(500, [90, 100, 110]));
        $this->assertNull($service->zScore(500, [90, 100]));
        $this->assertNull($service->zScore(500, [100, 100, 100]));
        $this->assertLessThanOrEqual(2, abs((float) $service->zScore(105, [90, 100, 110])));
    }

    public function test_anomaly_notification_is_sent_only_to_admins(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $owner = User::factory()->create(['role' => UserRole::OWNER]);
        $equipment = Equipment::factory()->create(['name' => 'Inverter', 'owner_id' => $owner->id]);
        foreach ([90, 100, 110] as $cost) {
            Maintenance::factory()->create(['equipment_id' => $equipment->id, 'cost' => $cost]);
        }
        $outlier = Maintenance::factory()->create(['equipment_id' => $equipment->id, 'cost' => 500]);

        app(MaintenanceCostAnomalyService::class)->evaluateAndNotify($outlier);

        Notification::assertSentTo($admin, AbnormalMaintenanceCostNotification::class);
        Notification::assertNotSentTo($owner, AbnormalMaintenanceCostNotification::class);
    }

    public function test_repair_estimation_uses_category_statistics_and_global_fallback(): void
    {
        $owner = User::factory()->create(['role' => UserRole::OWNER]);
        $category = Category::factory()->create();
        $otherCategory = Category::factory()->create();
        $categoryEquipment = Equipment::factory()->create(['name' => 'Panel', 'owner_id' => $owner->id, 'category_id' => $category->id]);
        $otherEquipment = Equipment::factory()->create(['name' => 'Battery', 'owner_id' => $owner->id, 'category_id' => $otherCategory->id]);
        $this->damage($categoryEquipment->id, 100);
        $this->damage($categoryEquipment->id, 200);
        $this->damage($categoryEquipment->id, 600);
        $this->damage($otherEquipment->id, 900);

        $service = new RepairCostEstimatorService;
        $categoryEstimate = $service->estimate($category->id, 'moderate');
        $fallback = $service->estimate(99, 'moderate');

        $this->assertSame(300.0, $categoryEstimate['average']);
        $this->assertSame(200.0, $categoryEstimate['median']);
        $this->assertSame([100.0, 600.0], [$categoryEstimate['minimum'], $categoryEstimate['maximum']]);
        $this->assertSame('global', $fallback['source']);
        $this->assertGreaterThan(0, $service->estimate(null, 'minor')['average']);
    }

    private function damage(int $equipmentId, float $cost): void
    {
        $inspection = Inspection::factory()->create(['equipment_id' => $equipmentId]);
        Damage::factory()->create([
            'inspection_id' => $inspection->id,
            'equipment_id' => $equipmentId,
            'inspection_checklist_item_id' => null,
            'severity' => 'moderate',
            'repair_cost' => $cost,
        ]);
    }
}
