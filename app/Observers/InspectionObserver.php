<?php

namespace App\Observers;

use App\Models\Equipment;
use App\Models\Inspection;

class InspectionObserver
{
    public function created(Inspection $inspection): void
    {
        $this->updateEquipmentStatus($inspection);
    }

    public function updated(Inspection $inspection): void
    {
        $this->updateEquipmentStatus($inspection);
    }

    private function updateEquipmentStatus(Inspection $inspection): void
    {
        if ($inspection->damage_detected) {
            $equipment = $inspection->equipment;
            if ($equipment && $equipment->status !== 'maintenance') {
                $equipment->update(['status' => 'maintenance']);
            }
        }
    }
}