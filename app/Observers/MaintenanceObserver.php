<?php

namespace App\Observers;

use App\Models\Equipment;
use App\Models\Maintenance;

class MaintenanceObserver
{
    public function updated(Maintenance $maintenance): void
    {
        $this->updateEquipmentStatus($maintenance);
    }

    private function updateEquipmentStatus(Maintenance $maintenance): void
    {
        $equipment = $maintenance->equipment;
        if (! $equipment) {
            return;
        }

        // When maintenance is completed, set equipment back to available
        if ($maintenance->status === 'completed' && $equipment->status === 'maintenance') {
            $equipment->update(['status' => 'available']);
        }
        // When maintenance starts (planned -> in_progress), set to maintenance
        elseif (in_array($maintenance->status, ['planned', 'in_progress']) && $equipment->status === 'available') {
            $equipment->update(['status' => 'maintenance']);
        }
    }
}