<?php

namespace Database\Factories;

use App\Models\Maintenance;
use App\Models\MaintenanceReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MaintenanceReport> */
class MaintenanceReportFactory extends Factory
{
    protected $model = MaintenanceReport::class;

    public function definition(): array
    {
        return [
            'maintenance_id' => Maintenance::factory(),
            'diagnosis' => fake()->sentence(),
            'actions_taken' => fake()->paragraph(),
            'parts_replaced' => fake()->optional()->words(3, true),
            'technician_notes' => fake()->optional()->sentence(),
            'report_date' => today(),
        ];
    }
}
