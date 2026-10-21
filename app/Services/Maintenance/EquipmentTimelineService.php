<?php

namespace App\Services\Maintenance;

use App\Models\Inspection;
use App\Models\Maintenance;
use Illuminate\Support\Collection;

class EquipmentTimelineService
{
    public function forEquipment(int $equipmentId): Collection
    {
        $maintenances = Maintenance::query()->where('equipment_id', $equipmentId)->get()->map(fn (Maintenance $maintenance): array => [
            'type' => 'maintenance',
            'date' => $maintenance->start_date,
            'title' => $maintenance->reason,
            'status' => $maintenance->status,
            'record' => $maintenance,
        ]);
        $inspections = Inspection::query()->where('equipment_id', $equipmentId)->get()->map(fn (Inspection $inspection): array => [
            'type' => 'inspection',
            'date' => $inspection->inspection_date,
            'title' => $inspection->damage_detected ? __('Inspection with damage') : __('Inspection completed'),
            'status' => $inspection->damage_detected ? 'damage' : 'passed',
            'record' => $inspection,
        ]);

        return $maintenances->concat($inspections)->sortByDesc('date')->values();
    }
}
