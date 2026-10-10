<?php

namespace App\Observers;

use App\Models\Inspection;
use App\Models\Maintenance;

class InspectionObserver
{
    public function created(Inspection $inspection): void
    {
        $this->handleDamageDetection($inspection);
    }

    public function updated(Inspection $inspection): void
    {
        // Only react when the damage flag itself changed. Editing a comment on an
        // old damaged inspection must not push repaired equipment back to maintenance.
        if ($inspection->wasChanged('damage_detected')) {
            $this->handleDamageDetection($inspection);
        }
    }

    private function handleDamageDetection(Inspection $inspection): void
    {
        if (! $inspection->damage_detected) {
            return;
        }

        $equipment = $inspection->equipment;
        if (! $equipment) {
            return;
        }

        // Set equipment status to maintenance
        if ($equipment->status !== 'maintenance') {
            $equipment->update(['status' => 'maintenance']);
        }

        // Check if there's already an in-progress maintenance for this equipment from this rental
        $existingMaintenance = Maintenance::where('equipment_id', $inspection->equipment_id)
            ->where('rental_id', $inspection->rental_id)
            ->where('status', 'in_progress')
            ->first();

        if (! $existingMaintenance) {
            // Create a new maintenance record linked to the equipment and rental
            $maintenance = Maintenance::create([
                'equipment_id' => $inspection->equipment_id,
                'rental_id' => $inspection->rental_id,
                'start_date' => now()->toDateString(),
                'reason' => 'Damage detected during return inspection: '.($inspection->comments ?? 'No comments provided'),
                'status' => 'in_progress',
                'cost' => 0,
                'notes' => 'Auto-created from return inspection #'.$inspection->id,
            ]);

            // Optionally, we could store the maintenance_id on the inspection
            // but that would require a migration to add maintenance_id to inspections table
        }
    }
}
