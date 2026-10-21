<?php

namespace App\Notifications;

use App\Models\Maintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AbnormalMaintenanceCostNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Maintenance $maintenance, public readonly float $zScore) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Abnormal maintenance cost detected'))
            ->line(__('A maintenance cost is statistically unusual.'))
            ->line(__('Equipment').': #'.$this->maintenance->equipment_id)
            ->line(__('Cost (TND)').': '.$this->maintenance->cost)
            ->line('Z-score: '.number_format($this->zScore, 2));
    }
}
