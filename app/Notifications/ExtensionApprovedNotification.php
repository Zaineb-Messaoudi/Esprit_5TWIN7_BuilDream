<?php

namespace App\Notifications;

use App\Models\RentalExtension;

/**
 * Fired when the owner approves a rental extension.
 * Delivered to the buyer.
 */
class ExtensionApprovedNotification extends BaseNotification
{
    public function __construct(
        public RentalExtension $extension,
    ) {
        $this->recipientId = $this->extension->rental->user_id;
    }

    public function title(): string
    {
        return __('Extension approved');
    }

    public function body(): string
    {
        return __('Your extension for :equipment has been approved until :end.', [
            'equipment' => $this->extension->rental->equipment->name,
            'end' => $this->extension->new_end_date->format('M j, Y'),
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
        return route('front.my-rental-detail', $this->extension->rental->reference, absolute: false);
    }

    public function actionLabel(): ?string
    {
        return __('View rental');
    }

    public function emailPreferenceKey(): string
    {
        return 'extension_approved';
    }
}
