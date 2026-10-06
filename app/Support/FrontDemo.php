<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Equipment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Front Office catalogue adapter.
 *
 * The public pages consume a small view-model shape, so this class keeps that
 * presentation contract while sourcing real records from Eloquent. The static
 * fallback keeps the public template usable before migrations or seeders run.
 */
class FrontDemo
{
    public static function categories(): Collection
    {
        if (Schema::hasTable('categories') && Schema::hasTable('equipment') && ($categories = Category::query()
            ->where('status', 'active')
            ->with(['equipment' => fn ($query) => $query->whereNotNull('image_url')->latest()])
            ->orderBy('name')
            ->get())->isNotEmpty()) {
            return $categories->map(fn (Category $category) => (object) [
                'id' => $category->id,
                'icon' => self::categoryIcon($category->name),
                'name' => $category->name,
                'description' => $category->description,
                'technology' => self::categoryTechnology($category->name),
                // Prefer a real product photo from the category so the visual is accurate.
                'image' => $category->image_url ?: $category->equipment->first()?->image_url ?: self::categoryImageUrl($category->name),
            ]);
        }

        return self::fallbackCategories();
    }

    private static function fallbackCategories(): Collection
    {
        return collect([
            ['id' => 1, 'icon' => '☀️', 'name' => 'Portable solar panels', 'description' => 'Foldable panels for camping, vans and off-grid weekends.', 'technology' => 'Monocrystalline', 'image' => self::categoryImageUrl('Portable solar panels')],
            ['id' => 2, 'icon' => '🔋', 'name' => 'Batteries', 'description' => 'Power stations from 500 Wh to 2 kWh for fridges, lights and tools.', 'technology' => 'Lithium-ion', 'image' => self::categoryImageUrl('Batteries')],
            ['id' => 3, 'icon' => '🌬️', 'name' => 'Small wind turbines', 'description' => 'Compact turbines to generate clean power where the wind blows.', 'technology' => 'Horizontal axis', 'image' => self::categoryImageUrl('Small wind turbines')],
        ])->map(fn (array $category) => (object) $category);
    }

    public static function equipment(): Collection
    {
        if (Schema::hasTable('equipment') && ($equipment = self::publishedEquipmentQuery()->latest()->get())->isNotEmpty()) {
            return $equipment->map(fn (Equipment $item) => self::mapEquipment($item));
        }

        $categories = self::fallbackCategories()->keyBy('id');

        return collect([
            [2, 'Portable battery 1000 Wh', 'Voltix', 'PB-1000', 18, 'Tunis', 'available', 500, 1000, 'Good', 'Sami'],
            [1, 'Foldable solar panel 200 W', 'SunFold', 'SF-200', 9, 'La Marsa', 'available', 200, null, 'New', 'Amira'],
            [3, 'Portable wind turbine 400 W', 'Aeolus', 'AW-400', 14, 'Bizerte', 'available', 400, null, 'Good', 'Karim'],
            [2, 'Power station 2000 Wh', 'Voltix', 'PB-2000', 30, 'Sousse', 'available', 1200, 2000, 'New', 'Yasmine'],
            [1, 'Solar kit 2 × 100 W', 'SunFold', 'SF-100x2', 11, 'Nabeul', 'maintenance', 200, null, 'Fair', 'Mehdi'],
            [2, 'Compact battery 500 Wh', 'Voltix', 'PB-500', 10, 'Ariana', 'available', 300, 500, 'Good', 'Nour'],
        ])->map(fn (array $row, int $index) => (object) [
            'id' => $index + 1, 'name' => $row[1], 'brand' => $row[2], 'model' => $row[3],
            'price_per_day' => $row[4], 'location' => $row[5], 'status' => $row[6], 'condition' => $row[9],
            'owner' => $row[10], 'image' => self::categoryImage($row[0]), 'category' => $categories[$row[0]],
            'energyProfile' => (object) ['power_watts' => $row[7], 'capacity_wh' => $row[8], 'voltage' => '12–24 V', 'efficiency' => 92, 'technology' => $categories[$row[0]]->technology, 'max_output' => $row[7], 'operating_duration' => $row[8] ? 8 : null],
        ]);
    }

    /** Query used by the public catalogue so filtering and pagination stay in SQL. */
    public static function publishedEquipmentQuery()
    {
        return Equipment::query()
            ->where('approval_status', 'published')
            ->where('status', '!=', 'inactive')
            ->with(['category', 'owner', 'energyProfile']);
    }

    /** Convert an Eloquent listing into the public catalogue view model. */
    public static function equipmentViewModel(Equipment $item): object
    {
        return self::mapEquipment($item);
    }

    /** Resolve one public listing from the database, with the demo fallback only before seeding. */
    public static function findEquipment(int $id): ?object
    {
        if (Schema::hasTable('equipment') && Equipment::query()->exists()) {
            $item = self::publishedEquipmentQuery()->find($id);

            return $item ? self::mapEquipment($item) : null;
        }

        return self::equipment()->firstWhere('id', $id);
    }

    private static function mapEquipment(Equipment $item): object
    {
        $item->loadMissing(['category', 'owner', 'energyProfile']);
        $categoryModel = $item->category;
        $categoryName = $categoryModel?->name ?: __('Uncategorized');
        $category = (object) [
            'id' => $categoryModel?->id, 'name' => $categoryName,
            'description' => $categoryModel?->description, 'icon' => self::categoryIcon($categoryName),
            'technology' => $item->energyProfile?->technology ?: self::categoryTechnology($categoryName),
        ];

        return (object) [
            'id' => $item->id, 'name' => $item->name, 'brand' => $item->brand, 'model' => $item->model,
            'description' => $item->description, 'price_per_day' => $item->price_per_day, 'location' => $item->location,
            'status' => $item->status, 'condition' => ucfirst($item->condition ?: 'good'), 'owner' => $item->owner?->name ?: __('SolarShare owner'),
            'image' => $item->image_url ?: self::categoryImage($category->name), 'category' => $category,
            'energyProfile' => (object) [
                'power_watts' => $item->energyProfile?->power_watts,
                'capacity_wh' => $item->energyProfile?->capacity_wh,
                'voltage' => $item->energyProfile?->voltage,
                'efficiency' => $item->energyProfile?->efficiency,
                'technology' => $category->technology,
                'max_output' => $item->energyProfile?->max_output,
                'operating_duration' => $item->energyProfile?->operating_duration,
            ],
        ];
    }

    private static function categoryIcon(string $name): string
    {
        $name = strtolower($name);

        return match (true) {
            str_contains($name, 'batter') || str_contains($name, 'power station') => '🔋',
            str_contains($name, 'wind') => '🌬️',
            str_contains($name, 'hydro') || str_contains($name, 'marine') => '💧',
            str_contains($name, 'biomass') => '🌾',
            str_contains($name, 'geothermal') || str_contains($name, 'thermal') => '♨️',
            str_contains($name, 'hydrogen') || str_contains($name, 'fuel') => '⚗️',
            str_contains($name, 'monitor') => '📊',
            str_contains($name, 'charge') => '⚡',
            default => '☀️',
        };
    }

    private static function categoryTechnology(string $name): string
    {
        $name = strtolower($name);

        return match (true) {
            str_contains($name, 'batter') || str_contains($name, 'power station') => 'Lithium-ion',
            str_contains($name, 'wind') => 'Horizontal axis',
            str_contains($name, 'hydrogen') || str_contains($name, 'fuel') => 'PEM fuel cell',
            str_contains($name, 'geothermal') => 'Ground-source',
            str_contains($name, 'charge') => 'MPPT',
            str_contains($name, 'thermal') => 'Solar thermal',
            str_contains($name, 'hydro') => 'Pelton',
            str_contains($name, 'biomass') => 'Biomass pellets',
            str_contains($name, 'marine') => 'Point absorber',
            str_contains($name, 'monitor') => 'Wi-Fi monitoring',
            default => 'Monocrystalline',
        };
    }

    private static function categoryImage(int|string $category): string
    {
        if (is_string($category)) {
            $category = strtolower($category);
            return str_contains($category, 'battery')
                ? 'images/front/battery-station.svg'
                : (str_contains($category, 'wind') ? 'images/front/wind-turbine.svg' : 'images/front/solar-panel.svg');
        }

        return match ($category) {
            2 => 'images/front/battery-station.svg',
            3 => 'images/front/wind-turbine.svg',
            default => 'images/front/solar-panel.svg',
        };
    }

    /** A real editorial image for the category rail and catalogue discovery UI. */
    private static function categoryImageUrl(string $name): string
    {
        $name = strtolower($name);

        return match (true) {
            str_contains($name, 'wind') => 'https://images.unsplash.com/photo-1466611653911-95081537e5b7?auto=format&fit=crop&w=1000&q=85',
            str_contains($name, 'hydro') || str_contains($name, 'marine') => 'https://images.unsplash.com/photo-1531206715517-5c0ba140b2b8?auto=format&fit=crop&w=1000&q=85',
            str_contains($name, 'biomass') => 'https://images.unsplash.com/photo-1516939884455-1445c8652f83?auto=format&fit=crop&w=1000&q=85',
            str_contains($name, 'geothermal') => 'https://images.unsplash.com/photo-1508514177221-188b1cf16e9d?auto=format&fit=crop&w=1000&q=85',
            str_contains($name, 'hydrogen') || str_contains($name, 'fuel') => 'https://images.unsplash.com/photo-1473341304170-971dccb5ac1e?auto=format&fit=crop&w=1000&q=85',
            str_contains($name, 'batter') || str_contains($name, 'power station') => 'https://www.everything4wd.com.au/assets/full/PS800.webp',
            str_contains($name, 'thermal') => 'https://images.unsplash.com/photo-1497435334941-8c899ee9e8e9?auto=format&fit=crop&w=1000&q=85',
            str_contains($name, 'monitor') || str_contains($name, 'charge') => 'https://images.unsplash.com/photo-1558008258-3256797b43f3?auto=format&fit=crop&w=1000&q=85',
            default => 'https://images.unsplash.com/photo-1509391366360-2e959784a276?auto=format&fit=crop&w=1000&q=85',
        };
    }
}
