<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Static demo data for the Front Office template (no database involved).
 * Property and relation names mirror the Category / Equipment / EnergyProfile
 * entities so views keep working when real models replace this class.
 */
class FrontDemo
{
    public static function categories(): Collection
    {
        return collect([
            ['id' => 1, 'icon' => '☀️', 'name' => 'Portable solar panels', 'description' => 'Foldable panels for camping, vans and off-grid weekends.', 'technology' => 'Monocrystalline'],
            ['id' => 2, 'icon' => '🔋', 'name' => 'Batteries', 'description' => 'Power stations from 500 Wh to 2 kWh for fridges, lights and tools.', 'technology' => 'Lithium-ion'],
            ['id' => 3, 'icon' => '🌬️', 'name' => 'Small wind turbines', 'description' => 'Compact turbines to generate clean power where the wind blows.', 'technology' => 'Horizontal axis'],
        ])->map(fn (array $c) => (object) $c);
    }

    public static function equipment(): Collection
    {
        $categories = self::categories()->keyBy('id');

        // [category, name, brand, model, price/day, city, status, power W, capacity Wh, condition, owner]
        return collect([
            [2, 'Portable battery 1000 Wh', 'Voltix', 'PB-1000', 18, 'Tunis', 'available', 500, 1000, 'Good', 'Sami'],
            [1, 'Foldable solar panel 200 W', 'SunFold', 'SF-200', 9, 'La Marsa', 'available', 200, null, 'New', 'Amira'],
            [3, 'Portable wind turbine 400 W', 'Aeolus', 'AW-400', 14, 'Bizerte', 'available', 400, null, 'Good', 'Karim'],
            [2, 'Power station 2000 Wh', 'Voltix', 'PB-2000', 30, 'Sousse', 'available', 1200, 2000, 'New', 'Yasmine'],
            [1, 'Solar kit 2 × 100 W', 'SunFold', 'SF-100x2', 11, 'Nabeul', 'maintenance', 200, null, 'Fair', 'Mehdi'],
            [2, 'Compact battery 500 Wh', 'Voltix', 'PB-500', 10, 'Ariana', 'available', 300, 500, 'Good', 'Nour'],
        ])->map(fn (array $r, int $i) => (object) [
            'id' => $i + 1,
            'name' => $r[1],
            'brand' => $r[2],
            'model' => $r[3],
            'price_per_day' => $r[4],
            'location' => $r[5],
            'status' => $r[6],
            'condition' => $r[9],
            'owner' => $r[10],
            'image' => match ($r[0]) {
                1 => 'images/front/solar-panel.svg',
                2 => 'images/front/battery-station.svg',
                3 => 'images/front/wind-turbine.svg',
            },
            'category' => $categories[$r[0]],
            'energyProfile' => (object) [
                'power_watts' => $r[7],
                'capacity_wh' => $r[8],
                'voltage' => '12–24 V',
                'technology' => $categories[$r[0]]->technology,
            ],
        ]);
    }
}
