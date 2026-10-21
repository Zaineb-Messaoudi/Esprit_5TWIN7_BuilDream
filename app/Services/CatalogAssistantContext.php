<?php

namespace App\Services;

use App\Models\Equipment;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class CatalogAssistantContext
{
    private const KNOWN_CITIES = [
        'tunis', 'ariana', 'manouba', 'ben arous', 'nabeul', 'hammamet', 'zaghouan', 'bizerte',
        'beja', 'jendouba', 'siliana', 'sousse', 'monastir', 'mahdia', 'sfax', 'kairouan',
        'kasserine', 'gabes', 'medenine', 'djerba', 'tataouine', 'gafsa', 'tozeur', 'kebili',
        'le kef', 'sidi bouzid', 'carthage', 'la marsa',
    ];

    /** @param list<array{role: string, text: string}> $history */
    public function forConversation(string $prompt, array $history): string
    {
        if (! Schema::hasTable('equipment')) {
            return $this->encode(['catalogue' => 'unavailable', 'items' => []]);
        }

        $queryText = Str::lower($prompt.' '.collect($history)
            ->where('role', 'user')
            ->pluck('text')
            ->take(-6)
            ->implode(' '));
        $city = collect(self::KNOWN_CITIES)->first(fn (string $candidate) => preg_match('/\b'.preg_quote($candidate, '/').'\b/u', $queryText) === 1);

        try {
            $query = Equipment::query()
                ->where('approval_status', 'published')
                ->where('status', 'available')
                ->when($city, fn ($builder) => $builder->where('location', 'like', '%'.$city.'%'));

            $count = (clone $query)->count();
            $items = $query->with(['category:id,name', 'energyProfile:equipment_id,power_watts,capacity_wh'])
                ->orderBy('name')
                ->limit(12)
                ->get(['id', 'category_id', 'name', 'brand', 'model', 'location', 'price_per_day', 'status'])
                ->map(fn (Equipment $equipment) => [
                    'id' => $equipment->id,
                    'name' => $equipment->name,
                    'brand' => $equipment->brand,
                    'model' => $equipment->model,
                    'category' => $equipment->category?->name,
                    'location' => $equipment->location,
                    'price_per_day_tnd' => (float) $equipment->price_per_day,
                    'status' => $equipment->status,
                    'power_watts' => $equipment->energyProfile?->power_watts,
                    'capacity_wh' => $equipment->energyProfile?->capacity_wh,
                ])
                ->values()
                ->all();

            return $this->encode([
                'catalogue' => 'live database snapshot',
                'requested_city' => $city,
                'matching_published_available_count' => $count,
                'items_shown' => count($items),
                'items' => $items,
                'date_availability' => 'not checked; reservation conflicts are checked when the user submits dates on the equipment reservation form',
            ]);
        } catch (Throwable) {
            return $this->encode(['catalogue' => 'temporarily unavailable', 'requested_city' => $city, 'items' => []]);
        }
    }

    private function encode(array $context): string
    {
        return json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE) ?: '{}';
    }
}
