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
            'diagnosis' => $this->faker->sentence(),
            'actions_taken' => $this->faker->paragraph(),
            'parts_replaced' => $this->faker->optional()->words(3, true),
            'technician_notes' => $this->faker->optional()->sentence(),
            'report_date' => today(),
        ];
    }
}
