<?php

namespace Tests\Unit;

use App\Services\Maintenance\EquipmentHealthScoreService;
use Tests\TestCase;

class EquipmentHealthScoreServiceTest extends TestCase
{
    private EquipmentHealthScoreService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(EquipmentHealthScoreService::class);
        config(['maintenance.health_score.weights' => ['age' => 0.20, 'damage' => 0.35, 'maintenance' => 0.25, 'rentals' => 0.20]]);
    }

    public function test_new_equipment_without_history_scores_one_hundred(): void
    {
        $result = $this->service->calculate(['age_years' => 0, 'damage_severities' => [], 'days_since_last_maintenance' => null, 'rental_count' => 0]);

        $this->assertSame(100, $result['score']);
        $this->assertSame('healthy', $result['level']);
        $this->assertSame(['age' => 100.0, 'damage' => 100.0, 'maintenance' => 100.0, 'rentals' => 100.0], $result['components']);
    }

    public function test_nominal_score_combines_all_weighted_factors(): void
    {
        $result = $this->service->calculate(['age_years' => 4, 'damage_severities' => ['minor', 'severe'], 'days_since_last_maintenance' => 100, 'rental_count' => 10]);

        $this->assertSame(['age' => 80.0, 'damage' => 52.0, 'maintenance' => 80.0, 'rentals' => 80.0], $result['components']);
        $this->assertSame(70, $result['score']);
        $this->assertSame('warning', $result['level']);
    }

    public function test_severely_degraded_equipment_approaches_zero(): void
    {
        $result = $this->service->calculate(['age_years' => 100, 'damage_severities' => array_fill(0, 10, 'severe'), 'days_since_last_maintenance' => 1000, 'rental_count' => 100]);

        $this->assertSame(0, $result['score']);
        $this->assertSame('critical', $result['level']);
    }

    public function test_inputs_and_result_are_clamped_to_zero_and_one_hundred(): void
    {
        $result = $this->service->calculate(['age_years' => -2, 'damage_severities' => [], 'days_since_last_maintenance' => -10, 'rental_count' => -1]);

        $this->assertSame(100, $result['score']);
        $this->assertGreaterThanOrEqual(0, $result['score']);
        $this->assertLessThanOrEqual(100, $result['score']);
    }

    public function test_badge_thresholds_include_exact_boundary_values(): void
    {
        config(['maintenance.health_score.weights' => ['age' => 1, 'damage' => 0, 'maintenance' => 0, 'rentals' => 0]]);

        $healthy = $this->service->calculate(['age_years' => 5]);
        $warning = $this->service->calculate(['age_years' => 12]);

        $this->assertSame(75, $healthy['score']);
        $this->assertSame('healthy', $healthy['level']);
        $this->assertSame(40, $warning['score']);
        $this->assertSame('warning', $warning['level']);
    }

    public function test_older_equipment_without_maintenance_or_rentals_is_supported(): void
    {
        $result = $this->service->calculate(['age_years' => 2, 'damage_severities' => [], 'days_since_last_maintenance' => null, 'rental_count' => 0]);

        $this->assertSame(0.0, $result['components']['maintenance']);
        $this->assertSame(100.0, $result['components']['rentals']);
        $this->assertSame(73, $result['score']);
        $this->assertSame('warning', $result['level']);
    }
}
