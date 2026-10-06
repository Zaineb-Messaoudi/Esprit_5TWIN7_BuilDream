<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EquipmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'Panneau solaire 400W',
                'Onduleur hybride 5kW',
                'Batterie lithium 10kWh',
                'Régulateur MPPT 60A',
                'Kit solaire résidentiel',
                'Chargeur solaire portable',
            ]),
        ];
    }
}