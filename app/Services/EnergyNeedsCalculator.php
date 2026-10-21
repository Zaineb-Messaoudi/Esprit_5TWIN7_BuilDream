<?php

namespace App\Services;

class EnergyNeedsCalculator
{
    private const INVERTER_EFFICIENCY = 0.9;

    private const BATTERY_DEPTH_OF_DISCHARGE = 0.8;

    private const PANEL_SYSTEM_EFFICIENCY = 0.75;

    private const INVERTER_MARGIN = 1.2;

    private const DEFAULT_SUN_HOURS = 5.0;

    /**
     * Typical values. Hours are the effective daily hours of use.
     *
     * @return array<string, array{label: string, watts: int, hours: float, surge: float, icon: string, key: string}>
     */
    public function appliances(): array
    {
        $appliances = [
            'led_lamp' => ['label' => 'LED lamp', 'watts' => 10, 'hours' => 5.0, 'surge' => 1.0, 'icon' => '💡'],
            'fridge' => ['label' => 'Refrigerator', 'watts' => 150, 'hours' => 8.0, 'surge' => 3.0, 'icon' => '🧊'],
            'tv' => ['label' => 'Television', 'watts' => 80, 'hours' => 4.0, 'surge' => 1.0, 'icon' => '📺'],
            'laptop' => ['label' => 'Laptop', 'watts' => 60, 'hours' => 6.0, 'surge' => 1.0, 'icon' => '💻'],
            'phone_charger' => ['label' => 'Phone charger', 'watts' => 10, 'hours' => 3.0, 'surge' => 1.0, 'icon' => '🔌'],
            'fan' => ['label' => 'Fan', 'watts' => 50, 'hours' => 6.0, 'surge' => 1.5, 'icon' => '🌬️'],
            'router' => ['label' => 'Internet router', 'watts' => 12, 'hours' => 24.0, 'surge' => 1.0, 'icon' => '📶'],
            'water_pump' => ['label' => 'Water pump', 'watts' => 750, 'hours' => 1.0, 'surge' => 3.0, 'icon' => '💧'],
            'power_tool' => ['label' => 'Power tool', 'watts' => 600, 'hours' => 1.0, 'surge' => 2.0, 'icon' => '🔧'],
            'washing_machine' => ['label' => 'Washing machine', 'watts' => 500, 'hours' => 1.0, 'surge' => 2.0, 'icon' => '🧺'],
            'microwave' => ['label' => 'Microwave', 'watts' => 1000, 'hours' => 0.5, 'surge' => 1.0, 'icon' => '🍲'],
            'air_conditioner' => ['label' => 'Small air conditioner', 'watts' => 900, 'hours' => 4.0, 'surge' => 3.0, 'icon' => '❄️'],
        ];

        foreach ($appliances as $key => &$appliance) {
            $appliance['key'] = $key;
        }

        return $appliances;
    }

    /**
     * @param  list<array{key?: string, quantity?: int|string, hours?: float|int|string|null}>  $items
     * @return array<string, mixed>
     */
    public function calculate(array $items, float $autonomyDays = 1.0, ?float $sunHours = null): array
    {
        $autonomyDays = max(0.5, min(5.0, $autonomyDays));
        $sunHours = max(2.0, min(8.0, $sunHours ?? self::DEFAULT_SUN_HOURS));

        $catalog = $this->appliances();
        $lines = [];
        $dailyWh = 0.0;
        $runningW = 0.0;
        $maxSurgeExtra = 0.0;

        foreach ($items as $item) {
            $key = (string) ($item['key'] ?? '');

            if (! isset($catalog[$key])) {
                continue;
            }

            $appliance = $catalog[$key];
            $quantity = max(1, min(50, (int) ($item['quantity'] ?? 1)));
            $hasHours = isset($item['hours']) && $item['hours'] !== '';
            $hours = $hasHours ? max(0.0, min(24.0, (float) $item['hours'])) : $appliance['hours'];

            $wh = $appliance['watts'] * $quantity * $hours;
            $dailyWh += $wh;
            $runningW += $appliance['watts'] * $quantity;
            // Only one unit starts at a time, so one surge is enough.
            $maxSurgeExtra = max($maxSurgeExtra, $appliance['watts'] * ($appliance['surge'] - 1));

            $lines[] = [
                'key' => $key,
                'label' => $appliance['label'],
                'quantity' => $quantity,
                'watts' => $appliance['watts'],
                'hours' => $hours,
                'daily_wh' => round($wh, 1),
            ];
        }

        if ($lines === [] || $dailyWh <= 0) {
            return ['status' => 'empty', 'lines' => []];
        }

        $adjustedWh = $dailyWh / self::INVERTER_EFFICIENCY;

        return [
            'status' => 'ok',
            'lines' => $lines,
            'daily_wh' => round($dailyWh, 1),
            'daily_kwh' => round($dailyWh / 1000, 2),
            'running_w' => (int) round($runningW),
            'inverter_w' => $this->roundUp(($runningW + $maxSurgeExtra) * self::INVERTER_MARGIN, 100),
            'battery_wh' => $this->roundUp($adjustedWh * $autonomyDays / self::BATTERY_DEPTH_OF_DISCHARGE, 100),
            'panel_w' => $this->roundUp($adjustedWh / ($sunHours * self::PANEL_SYSTEM_EFFICIENCY), 50),
            'assumptions' => [
                'autonomy_days' => $autonomyDays,
                'sun_hours' => $sunHours,
                'inverter_efficiency' => self::INVERTER_EFFICIENCY,
                'battery_depth_of_discharge' => self::BATTERY_DEPTH_OF_DISCHARGE,
                'panel_system_efficiency' => self::PANEL_SYSTEM_EFFICIENCY,
                'inverter_margin' => self::INVERTER_MARGIN,
            ],
        ];
    }

    private function roundUp(float $value, int $step): int
    {
        return (int) (ceil(round($value / $step, 6)) * $step);
    }
}
