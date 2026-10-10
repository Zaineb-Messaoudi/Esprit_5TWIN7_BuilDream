<?php

namespace App\Contracts;

/**
 * Interface for notification classes.
 *
 * This interface is normally provided by the illuminate/notifications package.
 * We define it here to maintain compatibility with the Notifiable trait.
 */
interface Notification
{
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array;
}
