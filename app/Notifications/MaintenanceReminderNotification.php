<?php

namespace App\Notifications;

use App\Models\Maintenance;

/**
 * Fired when equipment maintenance is scheduled within the next 3 days.
 * Delivered to the equipment owner.
 */
class MaintenanceReminderNotification extends BaseNotification
{
    public function __construct(
        public Maintenance $maintenance,
    ) {
        $this->recipientId = $maintenance->equipment->owner_id;
    }

    public function title(): string
    {
        return __('Maintenance upcoming');
    }

    public function body(): string
    {
        return __('Maintenance for :equipment is scheduled for :date. Reason: :reason', [
            'equipment' => $this->maintenance->equipment->name,
            'date' => $this->maintenance->start_date->format('M j, Y'),
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
        return 'maintenance_reminder';
    }
}
