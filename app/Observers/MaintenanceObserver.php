<?php

namespace App\Observers;

use App\Models\Maintenance;

/**
 * Keeps Equipment.status in step with its maintenances:
 *  - a maintenance that is "in_progress" puts available equipment in "maintenance";
 *  - when a maintenance is "completed" and no other one is still in progress,
 *    equipment that was in "maintenance" becomes "available" again.
 * A merely "planned" maintenance does not take the equipment off the catalogue.
 */
class MaintenanceObserver
{
    public function created(Maintenance $maintenance): void
    {
        $this->syncEquipmentStatus($maintenance);
    }

    public function updated(Maintenance $maintenance): void
    {
        if ($maintenance->wasChanged('status')) {
            $this->syncEquipmentStatus($maintenance);
        }
    }

    private function syncEquipmentStatus(Maintenance $maintenance): void
    {
        $equipment = $maintenance->equipment;
        if (! $equipment) {
            return;
        }

        if ($maintenance->status === 'in_progress' && $equipment->status === 'available') {
            $equipment->update(['status' => 'maintenance']);

            return;
        }

        if ($maintenance->status === 'completed' && $equipment->status === 'maintenance') {
            $stillOpen = $equipment->maintenances()
                ->where('status', 'in_progress')
                ->whereKeyNot($maintenance->getKey())
                ->exists();

            if (! $stillOpen) {
                $equipment->update(['status' => 'available']);
            }
        }
    }
}
