<?php

namespace App\Observers;

use App\Models\Inspection;

class InspectionObserver
{
    public function created(Inspection $inspection): void
    {
        $this->flagEquipmentForMaintenance($inspection);
    }

    public function updated(Inspection $inspection): void
    {
        // Only react when the damage flag itself changed. Editing a comment on an
        // old damaged inspection must not push repaired equipment back to maintenance.
        if ($inspection->wasChanged('damage_detected')) {
            $this->flagEquipmentForMaintenance($inspection);
        }
    }

    private function flagEquipmentForMaintenance(Inspection $inspection): void
    {
        if (! $inspection->damage_detected) {
            return;
        }

        $equipment = $inspection->equipment;
        if ($equipment && $equipment->status !== 'maintenance') {
            $equipment->update(['status' => 'maintenance']);
        }
    }
}
