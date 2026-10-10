<?php

namespace App\Notifications;

use App\Models\RentalExtension;

/**
 * Fired when the owner rejects a rental extension.
 * Delivered to the buyer.
 */
class ExtensionRejectedNotification extends BaseNotification
{
    public function __construct(
        public RentalExtension $extension,
    ) {
        $this->recipientId = $this->extension->rental->user_id;
    }

    public function title(): string
    {
        return __('Extension declined');
    }

    public function body(): string
    {
        return __('Your extension request for :equipment was declined.', [
            'equipment' => $this->extension->rental->equipment->name,
        ]);
    }

    public function icon(): string
    {
        return 'x-circle';
    }

    public function color(): string
    {
        return 'error';
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
        return 'extension_rejected';
    }
}
