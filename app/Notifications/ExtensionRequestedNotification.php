<?php

namespace App\Notifications;

use App\Models\RentalExtension;

/**
 * Fired when a buyer requests to extend a rental.
 * Delivered to the equipment owner.
 */
class ExtensionRequestedNotification extends BaseNotification
{
    public function __construct(
        public RentalExtension $extension,
    ) {
        $this->recipientId = $this->extension->rental->equipment->owner_id;
    }

    public function title(): string
    {
        return __('Extension request');
    }

    public function body(): string
    {
        return __(':name requested an extension for :equipment until :end', [
            'name' => $this->extension->rental->user->name,
            'equipment' => $this->extension->rental->equipment->name,
            'end' => $this->extension->new_end_date->format('M j, Y'),
        ]);
    }

    public function icon(): string
    {
        return 'clock';
    }

    public function color(): string
    {
        return 'warning';
    }

    public function actionUrl(): ?string
    {
        return route('front.my-rental-detail', $this->extension->rental->reference, absolute: false);
    }

    public function actionLabel(): ?string
    {
        return __('Review request');
    }

    public function emailPreferenceKey(): string
    {
        return 'extension_requested';
    }
}
