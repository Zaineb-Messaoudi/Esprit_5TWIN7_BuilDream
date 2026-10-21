<?php

namespace Database\Factories;

use App\Models\Maintenance;
use App\Models\MaintenancePhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MaintenancePhoto> */
class MaintenancePhotoFactory extends Factory
{
    protected $model = MaintenancePhoto::class;

    public function definition(): array
    {
        return [
            'maintenance_id' => Maintenance::factory(),
            'type' => fake()->randomElement(['before', 'after']),
            'path' => 'maintenance/example/'.fake()->uuid().'.jpg',
            'original_name' => fake()->word().'.jpg',
            'mime_type' => 'image/jpeg',
            'size' => fake()->numberBetween(1000, 5000000),
        ];
    }
}
