<?php

namespace App\Notifications;

use App\Models\Maintenance;

/**
 * Fired when maintenance is completed and the equipment is available again.
 * Delivered to the equipment owner.
 */
class MaintenanceCompletedNotification extends BaseNotification
{
    public function __construct(
        public Maintenance $maintenance,
    ) {
        $this->recipientId = $maintenance->equipment->owner_id;
    }

    public function title(): string
    {
        return __('Maintenance completed');
    }

    public function body(): string
    {
        return __('Maintenance completed for :equipment. The equipment is available again.', [
            'equipment' => $this->maintenance->equipment->name,
        ]);
    }

    public function icon(): string
    {
        return 'check-circle';
    }

    public function color(): string
    {
        return 'success';
    }

    public function actionUrl(): ?string
    {
        return route('technical.maintenances.show', $this->maintenance, absolute: false);
    }

    public function actionLabel(): ?string
    {
        return __('View report');
    }

    public function emailPreferenceKey(): string
    {
        return 'maintenance_completed';
    }
}
