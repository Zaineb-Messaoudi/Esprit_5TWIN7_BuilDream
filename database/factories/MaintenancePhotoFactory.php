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
            'type' => $this->faker->randomElement(['before', 'after']),
            'path' => 'maintenance/example/'.$this->faker->uuid().'.jpg',
            'original_name' => $this->faker->word().'.jpg',
            'mime_type' => 'image/jpeg',
            'size' => $this->faker->numberBetween(1000, 5000000),
        ];
    }
}
