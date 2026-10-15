<?php

namespace App\Notifications;

use App\Models\Maintenance;

/**
 * Fired when maintenance starts on a piece of equipment.
 * Delivered to the equipment owner.
 */
class MaintenanceCreatedNotification extends BaseNotification
{
    public function __construct(
        public Maintenance $maintenance,
    ) {
        $this->recipientId = $maintenance->equipment->owner_id;
    }

    public function title(): string
    {
        return __('Maintenance started');
    }

    public function body(): string
    {
        return __('Maintenance started for :equipment: :reason', [
            'equipment' => $this->maintenance->equipment->name,
            'reason' => $this->maintenance->reason ?: __('Not specified'),
        ]);
    }

    public function icon(): string
    {
        return 'wrench';
    }

    public function color(): string
    {
        return 'warning';
    }

    public function actionUrl(): ?string
    {
        return route('technical.maintenances.show', $this->maintenance, absolute: false);
    }

    public function actionLabel(): ?string
    {
        return __('View maintenance');
    }

    public function emailPreferenceKey(): string
    {
        return 'maintenance_created';
    }
}
