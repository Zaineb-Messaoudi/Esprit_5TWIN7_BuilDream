<?php

namespace App\Services;

class EnergyEquipmentMatcher
{
    private const MAX_UNITS = 10;

    private const PER_ROLE = 3;

    /**
     * Rank catalogue listings by the daily cost needed to cover each need.
     *
     * @param  array<string, mixed>  $result  Output of EnergyNeedsCalculator::calculate().
     * @param  iterable<\App\Models\Equipment>  $listings
     * @return array{generation: list<array<string, mixed>>, storage: list<array<string, mixed>>, inverter: list<array<string, mixed>>}
     */
    public function match(array $result, iterable $listings): array
    {
        $roles = [
            'generation' => ['need' => (int) ($result['panel_w'] ?? 0), 'field' => 'power_watts'],
            'storage' => ['need' => (int) ($result['battery_wh'] ?? 0), 'field' => 'capacity_wh'],
            'inverter' => ['need' => (int) ($result['inverter_w'] ?? 0), 'field' => 'max_output'],
        ];

        $matches = ['generation' => [], 'storage' => [], 'inverter' => []];

        foreach ($listings as $listing) {
            $profile = $listing->energyProfile;

            if ($profile === null) {
                continue;
            }

            foreach ($roles as $role => $config) {
                $unitValue = (int) ($profile->{$config['field']} ?? 0);

                if ($unitValue <= 0 || $config['need'] <= 0) {
                    continue;
                }

                $units = (int) ceil($config['need'] / $unitValue);

                if ($units > self::MAX_UNITS) {
                    continue;
                }

                $price = (float) $listing->price_per_day;

                $matches[$role][] = [
                    'equipment_id' => $listing->id,
                    'name' => $listing->name,
                    'location' => $listing->location,
                    'category' => $listing->relationLoaded('category') ? $listing->category?->name : null,
                    'unit_value' => $unitValue,
                    'units' => $units,
                    'price_per_day' => $price,
                    'daily_cost' => round($price * $units, 2),
                ];
            }
        }

        foreach ($matches as $role => $rows) {
            usort($rows, fn (array $a, array $b): int => [$a['daily_cost'], $a['units']] <=> [$b['daily_cost'], $b['units']]);
            $matches[$role] = array_slice($rows, 0, self::PER_ROLE);
        }

        return $matches;
    }
}
