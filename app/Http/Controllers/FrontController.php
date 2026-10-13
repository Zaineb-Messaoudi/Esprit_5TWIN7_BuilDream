<?php

namespace App\Http\Controllers;

use App\Models\EnergyProfile;
use App\Models\Equipment;
use App\Support\FrontDemo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/** Public Front Office pages for browsing the Eloquent-backed catalogue. */
class FrontController extends Controller
{
    public function home(): View
    {
        return view('pages.front.home', [
            'title' => __('Rent and share renewable energy'),
            'categories' => FrontDemo::categories(),
        ]);
    }

    public function catalog(Request $request): View
    {
        $filters = validator($request->query(), [
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer'],
            'brand' => ['nullable', 'string', 'max:100'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'min_power' => ['nullable', 'integer', 'min:0'],
            'max_power' => ['nullable', 'integer', 'min:0'],
            'min_capacity' => ['nullable', 'integer', 'min:0'],
            'max_capacity' => ['nullable', 'integer', 'min:0'],
            'condition' => ['nullable', 'in:New,Good,Fair,Damaged'],
            'status' => ['nullable', 'in:available,unavailable,maintenance,inactive'],
            'technology' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'in:newest,name_asc,price_asc,price_desc,power_desc,capacity_desc'],
        ])->valid();

        // Keep the public template usable before the catalogue has been seeded.
        // Production requests use the SQL query below; this branch is only the
        // documented demo fallback used by the front-office preview.
        if (! Equipment::query()->exists()) {
            $fallback = FrontDemo::equipment()
                ->when($filters['q'] ?? null, fn (Collection $c, string $q) => $c->filter(fn ($i) => str_contains(mb_strtolower("{$i->name} {$i->brand} {$i->model} {$i->location} {$i->category->name} {$i->category->description} {$i->energyProfile->technology}"), mb_strtolower($q))))
                ->when($filters['category'] ?? null, fn (Collection $c, $id) => $c->where('category.id', (int) $id))
                ->when($filters['brand'] ?? null, fn (Collection $c, string $brand) => $c->filter(fn ($i) => str_contains(mb_strtolower($i->brand), mb_strtolower($brand))))
                ->when($filters['min_price'] ?? null, fn (Collection $c, $min) => $c->where('price_per_day', '>=', (float) $min))
                ->when($filters['max_price'] ?? null, fn (Collection $c, $max) => $c->where('price_per_day', '<=', (float) $max))
                ->when($filters['min_capacity'] ?? null, fn (Collection $c, $min) => $c->filter(fn ($i) => ($i->energyProfile->capacity_wh ?? 0) >= (int) $min))
                ->when($filters['condition'] ?? null, fn (Collection $c, $condition) => $c->where('condition', $condition))
                ->when($filters['location'] ?? null, fn (Collection $c, string $location) => $c->filter(fn ($i) => str_contains(mb_strtolower($i->location), mb_strtolower($location))));

            $page = LengthAwarePaginator::resolveCurrentPage();
            $equipment = new LengthAwarePaginator($fallback->forPage($page, 12)->values(), $fallback->count(), 12, $page, ['path' => request()->url(), 'query' => request()->query()]);

            return view('pages.front.catalog', [
                'title' => __('Equipment catalog'), 'categories' => FrontDemo::categories(), 'equipment' => $equipment,
                'filters' => $filters, 'conditions' => ['New', 'Good', 'Fair'], 'statuses' => ['available', 'maintenance', 'unavailable', 'inactive'],
                'technologies' => FrontDemo::equipment()->pluck('energyProfile.technology')->filter()->unique()->sort()->values(),
            ]);
        }

        $equipmentQuery = FrontDemo::publishedEquipmentQuery()
            ->when($filters['q'] ?? null, fn ($query, string $q) => $query->where(function ($query) use ($q): void {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('brand', 'like', "%{$q}%")
                    ->orWhere('model', 'like', "%{$q}%")
                    ->orWhere('location', 'like', "%{$q}%")
                    ->orWhereHas('category', fn ($category) => $category->where('name', 'like', "%{$q}%"))
                    ->orWhereHas('energyProfile', fn ($profile) => $profile->where('technology', 'like', "%{$q}%"));
            }))
            ->when($filters['category'] ?? null, fn ($query, $id) => $query->where('category_id', (int) $id))
            ->when($filters['brand'] ?? null, fn ($query, string $brand) => $query->where('brand', 'like', "%{$brand}%"))
            ->when($filters['min_price'] ?? null, fn ($query, $min) => $query->where('price_per_day', '>=', (float) $min))
            ->when($filters['max_price'] ?? null, fn ($query, $max) => $query->where('price_per_day', '<=', (float) $max))
            ->when($filters['min_power'] ?? null, fn ($query, $min) => $query->whereHas('energyProfile', fn ($profile) => $profile->where('power_watts', '>=', (int) $min)))
            ->when($filters['max_power'] ?? null, fn ($query, $max) => $query->whereHas('energyProfile', fn ($profile) => $profile->where('power_watts', '<=', (int) $max)))
            ->when($filters['min_capacity'] ?? null, fn ($query, $min) => $query->whereHas('energyProfile', fn ($profile) => $profile->where('capacity_wh', '>=', (int) $min)))
            ->when($filters['max_capacity'] ?? null, fn ($query, $max) => $query->whereHas('energyProfile', fn ($profile) => $profile->where('capacity_wh', '<=', (int) $max)))
            ->when($filters['condition'] ?? null, fn ($query, $condition) => $query->where('condition', strtolower($condition)))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['technology'] ?? null, fn ($query, string $technology) => $query->whereHas('energyProfile', fn ($profile) => $profile->where('technology', 'like', "%{$technology}%")))
            ->when($filters['location'] ?? null, fn ($query, string $location) => $query->where('location', 'like', "%{$location}%"));

        $equipmentQuery = match ($filters['sort'] ?? 'newest') {
            'price_asc' => $equipmentQuery->orderBy('price_per_day'),
            'price_desc' => $equipmentQuery->orderByDesc('price_per_day'),
            'power_desc' => $equipmentQuery->orderByDesc(EnergyProfile::select('power_watts')->whereColumn('energy_profiles.equipment_id', 'equipment.id')),
            'capacity_desc' => $equipmentQuery->orderByDesc(EnergyProfile::select('capacity_wh')->whereColumn('energy_profiles.equipment_id', 'equipment.id')),
            'name_asc' => $equipmentQuery->orderBy('name'),
            default => $equipmentQuery->latest(),
        };

        $equipment = $equipmentQuery->paginate(12)->withQueryString();
        $equipment->through(fn (\App\Models\Equipment $item) => FrontDemo::equipmentViewModel($item));

        return view('pages.front.catalog', [
            'title' => __('Equipment catalog'),
            'categories' => FrontDemo::categories(),
            'equipment' => $equipment,
            'filters' => $filters,
            'conditions' => ['New', 'Good', 'Fair'],
            'statuses' => ['available', 'maintenance', 'unavailable', 'inactive'],
            'technologies' => FrontDemo::equipment()->pluck('energyProfile.technology')->filter()->unique()->sort()->values(),
        ]);
    }

    public function equipmentMap(): View
    {
        $cityCoordinates = [
            'tunis' => [36.8065, 10.1815],
            'la marsa' => [36.8782, 10.3247],
            'sousse' => [35.8256, 10.6084],
            'sfax' => [34.7406, 10.7603],
            'gabes' => [33.8815, 10.0982],
            'bizerte' => [37.2744, 9.8739],
            'ariana' => [36.8665, 10.1647],
            'ben arous' => [36.7531, 10.2282],
            'monastir' => [35.7643, 10.8113],
            'hammamet' => [36.4000, 10.6167],
            'djerba' => [33.8076, 10.8451],
            'kélibia' => [36.8476, 11.0939],
            'kelibia' => [36.8476, 11.0939],
            'aïn draham' => [36.7750, 8.6833],
            'ain draham' => [36.7750, 8.6833],
            'le kef' => [36.1742, 8.7049],
            'kairouan' => [35.6781, 10.0963],
            'tabarka' => [36.9544, 8.7580],
            'mahdia' => [35.5047, 11.0622],
            'nabeul' => [36.4513, 10.7357],
        ];

        $equipment = FrontDemo::publishedEquipmentQuery()
            ->with(['category', 'energyProfile'])
            ->where('status', 'available')
            ->latest()
            ->get()
            ->map(function (Equipment $item) use ($cityCoordinates): array {
                $fallback = $cityCoordinates[mb_strtolower(trim((string) $item->location))] ?? null;

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'brand' => $item->brand,
                    'location' => $item->location,
                    'latitude' => (float) ($item->latitude ?? $fallback[0] ?? 36.8065),
                    'longitude' => (float) ($item->longitude ?? $fallback[1] ?? 10.1815),
                    'price' => number_format((float) $item->price_per_day, 2).' TND / day',
                    'url' => route('front.equipment.show', $item->id),
                ];
            });

        return view('pages.front.equipment-map', [
            'title' => __('Equipment map'),
            'equipment' => $equipment,
        ]);
    }

    public function show(int $id): View
    {
        $item = FrontDemo::findEquipment($id);
        abort_unless($item, 404);

        $related = FrontDemo::publishedEquipmentQuery()
            ->where('category_id', $item->category->id)
            ->where('id', '!=', $id)
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn (Equipment $equipment) => FrontDemo::equipmentViewModel($equipment));

        return view('pages.front.equipment', [
            'title' => $item->name,
            'item' => $item,
            'related' => $related,
        ]);
    }
}
