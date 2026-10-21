<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Services\EnergyEquipmentMatcher;
use App\Services\EnergyNeedsCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class EnergyCalculatorController extends Controller
{
    public function index(EnergyNeedsCalculator $calculator): View
    {
        return view('pages.front.energy-calculator', [
            'appliances' => $calculator->appliances(),
            'result' => null,
            'matches' => null,
            'input' => [],
        ]);
    }

    public function calculate(Request $request, EnergyNeedsCalculator $calculator, EnergyEquipmentMatcher $matcher): View
    {
        $data = $request->validate([
            'items' => ['nullable', 'array'],
            'items.*.selected' => ['nullable', 'boolean'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1', 'max:50'],
            'items.*.hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'autonomy_days' => ['nullable', 'numeric', 'min:0.5', 'max:5'],
            'sun_hours' => ['nullable', 'numeric', 'min:2', 'max:8'],
        ]);

        $items = [];
        foreach (($data['items'] ?? []) as $key => $row) {
            if (empty($row['selected'])) {
                continue;
            }

            $items[] = [
                'key' => (string) $key,
                'quantity' => $row['quantity'] ?? 1,
                'hours' => $row['hours'] ?? null,
            ];
        }

        $result = $calculator->calculate(
            $items,
            (float) ($data['autonomy_days'] ?? 1),
            isset($data['sun_hours']) ? (float) $data['sun_hours'] : null,
        );

        $matches = null;
        if ($result['status'] === 'ok') {
            $listings = Equipment::query()
                ->published()
                ->whereHas('energyProfile')
                ->with(['energyProfile', 'category'])
                ->limit(200)
                ->get();

            $matches = $matcher->match($result, $listings);
        }

        return view('pages.front.energy-calculator', [
            'appliances' => $calculator->appliances(),
            'result' => $result,
            'matches' => $matches,
            'input' => $request->all(),
        ]);
    }
}
