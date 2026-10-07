<?php

namespace App\Observers;

use App\Models\Maintenance;
use App\Models\MaintenanceReport;

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

            // Auto-create a maintenance report template when maintenance is completed
            $this->createMaintenanceReport($maintenance);
        }
    }

    private function createMaintenanceReport(Maintenance $maintenance): void
    {
        // Check if a report already exists
        if ($maintenance->report) {
            return;
        }

        // Create a basic maintenance report template
        MaintenanceReport::create([
            'maintenance_id' => $maintenance->id,
            'diagnosis' => $maintenance->reason ?? 'Maintenance performed',
            'actions_taken' => 'Maintenance completed on ' . now()->format('Y-m-d') . '. Details to be filled by technician.',
            'parts_replaced' => null,
            'technician_notes' => 'Auto-generated report template. Please fill in actual details.',
            'report_date' => now()->toDateString(),
        ]);
    }
}
