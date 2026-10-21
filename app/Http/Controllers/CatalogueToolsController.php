<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Support\FrontDemo;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/** Public catalogue tools built from the existing EnergyProfile data. */
class CatalogueToolsController extends Controller
{
    private const APPLIANCES = [
        'fridge' => ['label' => 'Fridge', 'watts' => 120, 'hours' => 8, 'icon' => '❄️'],
        'lights' => ['label' => 'LED lights', 'watts' => 10, 'hours' => 5, 'icon' => '💡'],
        'phone' => ['label' => 'Phone charging', 'watts' => 10, 'hours' => 3, 'icon' => '📱'],
        'laptop' => ['label' => 'Laptop', 'watts' => 60, 'hours' => 5, 'icon' => '💻'],
        'fan' => ['label' => 'Fan', 'watts' => 50, 'hours' => 6, 'icon' => '🌀'],
    ];

    public function energyCalculator(Request $request): View
    {
        $values = $this->calculatorValues($request);
        $appliances = collect(self::APPLIANCES)->map(function (array $appliance, string $key) use ($values): array {
            $quantity = $values[$key]['quantity'];
            $hours = $values[$key]['hours'];

            return $appliance + [
                'key' => $key,
                'quantity' => $quantity,
                'selected_hours' => $hours,
                'energy_wh' => $quantity * $appliance['watts'] * $hours,
            ];
        });

        $requiredWh = $request->boolean('calculate') ? $appliances->sum('energy_wh') : 0;
        $requiredPower = $request->boolean('calculate') ? $appliances->sum(fn (array $item) => $item['quantity'] * $item['watts']) : 0;
        $recommendations = $request->boolean('calculate') ? $this->recommendations($requiredWh, $requiredPower) : collect();

        return view('pages.front.energy-calculator', [
            'title' => __('Energy calculator'),
            'appliances' => $appliances,
            'requiredWh' => $requiredWh,
            'requiredPower' => $requiredPower,
            'recommendations' => $recommendations,
            'calculated' => $request->boolean('calculate'),
        ]);
    }

    public function compare(Request $request): View
    {
        $available = $this->availableEquipment();
        $ids = collect((array) $request->input('ids', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->take(3)
            ->values();

        $selected = $available->filter(fn ($equipment) => $ids->contains((int) $equipment->id))->values();

        return view('pages.front.equipment-compare', [
            'title' => __('Compare equipment'),
            'available' => $available,
            'selected' => $selected,
            'selectedIds' => $ids,
        ]);
    }

    private function calculatorValues(Request $request): array
    {
        return collect(self::APPLIANCES)->mapWithKeys(function (array $appliance, string $key) use ($request): array {
            return [$key => [
                'quantity' => max(0, min(10, (int) $request->input("{$key}_quantity", 1))),
                'hours' => max(0, min(24, (float) $request->input("{$key}_hours", $appliance['hours']))),
            ]];
        })->all();
    }

    private function recommendations(int|float $requiredWh, int|float $requiredPower): Collection
    {
        if (! $requiredWh) {
            return collect();
        }

        if (! app()->bound('db') || ! Equipment::query()->exists()) {
            return FrontDemo::equipment()->filter(function (object $equipment) use ($requiredWh, $requiredPower): bool {
                return $equipment->status === 'available'
                    && ($equipment->energyProfile?->capacity_wh ?? 0) >= $requiredWh
                    && ($equipment->energyProfile?->power_watts ?? 0) >= $requiredPower;
            })->take(6)->values();
        }

        return FrontDemo::publishedEquipmentQuery()
            ->where('status', 'available')
            ->whereHas('energyProfile', fn ($query) => $query
                ->where('capacity_wh', '>=', $requiredWh)
                ->where(function ($query) use ($requiredPower): void {
                    $query->where('power_watts', '>=', $requiredPower)->orWhere('max_output', '>=', $requiredPower);
                }))
            ->orderBy('price_per_day')
            ->limit(6)
            ->get()
            ->map(fn (Equipment $equipment) => FrontDemo::equipmentViewModel($equipment));
    }

    private function availableEquipment(): Collection
    {
        if (! app()->bound('db') || ! Equipment::query()->exists()) {
            return FrontDemo::equipment();
        }

        return FrontDemo::publishedEquipmentQuery()
            ->where('status', 'available')
            ->whereHas('energyProfile')
            ->orderBy('name')
            ->get()
            ->map(fn (Equipment $equipment) => FrontDemo::equipmentViewModel($equipment));
    }
}
