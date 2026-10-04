<?php

namespace App\Http\Controllers;

use App\Support\FrontDemo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/** Front Office template pages with demo data (no backend yet). */
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
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'min_power' => ['nullable', 'integer', 'min:0'],
            'min_capacity' => ['nullable', 'integer', 'min:0'],
            'condition' => ['nullable', 'in:New,Good,Fair'],
            'location' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'in:newest,price_asc,price_desc,power_desc'],
        ])->valid();

        $equipment = FrontDemo::equipment()
            ->when($filters['q'] ?? null, fn (Collection $c, string $q) => $c->filter(
                fn ($i) => str_contains(mb_strtolower("{$i->name} {$i->brand} {$i->model} {$i->location}"), mb_strtolower($q))
            ))
            ->when($filters['category'] ?? null, fn (Collection $c, $id) => $c->where('category.id', (int) $id))
            ->when($filters['max_price'] ?? null, fn (Collection $c, $max) => $c->where('price_per_day', '<=', (float) $max))
            ->when($filters['min_power'] ?? null, fn (Collection $c, $min) => $c->where('energyProfile.power_watts', '>=', (int) $min))
            ->when($filters['min_capacity'] ?? null, fn (Collection $c, $min) => $c->filter(
                fn ($i) => ($i->energyProfile->capacity_wh ?? 0) >= (int) $min
            ))
            ->when($filters['condition'] ?? null, fn (Collection $c, $condition) => $c->where('condition', $condition))
            ->when($filters['location'] ?? null, fn (Collection $c, string $location) => $c->filter(
                fn ($i) => str_contains(mb_strtolower($i->location), mb_strtolower($location))
            ));

        $equipment = match ($filters['sort'] ?? 'newest') {
            'price_asc' => $equipment->sortBy('price_per_day'),
            'price_desc' => $equipment->sortByDesc('price_per_day'),
            'power_desc' => $equipment->sortByDesc('energyProfile.power_watts'),
            default => $equipment,
        };

        return view('pages.front.catalog', [
            'title' => __('Equipment catalog'),
            'categories' => FrontDemo::categories(),
            'equipment' => $equipment->values(),
            'filters' => $filters,
            'conditions' => ['New', 'Good', 'Fair'],
        ]);
    }

    public function show(int $id): View
    {
        $all = FrontDemo::equipment();
        $item = $all->firstWhere('id', $id);
        abort_unless($item, 404);

        return view('pages.front.equipment', [
            'title' => $item->name,
            'item' => $item,
            'related' => $all->where('category.id', $item->category->id)->where('id', '!=', $id)->take(3),
        ]);
    }
}
