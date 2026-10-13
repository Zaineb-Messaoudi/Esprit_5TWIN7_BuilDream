<?php

namespace App\Notifications;

use App\Models\Inspection;
use App\Models\Rental;

/**
 * Fired when equipment is returned and inspected.
 *
 * The event carries both the owner and the buyer, so the listener notifies
 * each recipient separately. The notification itself is recipient-agnostic:
 * the deep link and the broadcast channel resolve to whoever is receiving it.
 */
class EquipmentReturnedNotification extends BaseNotification
{
    public function __construct(
        public Rental $rental,
        public Inspection $inspection,
    ) {
        // No fixed recipient: the listener sets recipientId per recipient.
    }

    public function title(): string
    {
        return $this->inspection->damage_detected
            ? __('Equipment returned — damage detected')
            : __('Equipment returned');
    }

    public function body(): string
    {
        return $this->inspection->damage_detected
            ? __('Equipment :equipment returned with damage. Rental :reference completed.', [
                'equipment' => $this->rental->equipment->name,
                'reference' => $this->rental->reference,
            ])
            : __('Equipment :equipment returned in good condition. Rental :reference completed.', [
                'equipment' => $this->rental->equipment->name,
                'reference' => $this->rental->reference,
            ]);
    }

    public function icon(): string
    {
        return $this->inspection->damage_detected ? 'alert-triangle' : 'check-circle';
    }

    public function color(): string
    {
        return $this->inspection->damage_detected ? 'error' : 'success';
    }

    public function actionUrl(): ?string
    {
        return route('technical.inspections.show', $this->inspection, absolute: false);
    }

    public function actionLabel(): ?string
    {
        return __('View inspection');
    }

    public function emailPreferenceKey(): string
    {
        return 'equipment_returned';
    }
}
