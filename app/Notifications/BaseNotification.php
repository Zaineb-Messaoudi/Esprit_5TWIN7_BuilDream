<?php

namespace App\Notifications;

use App\Contracts\Notification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Base class for all SolarShare notifications.
 *
 * Every concrete notification declares:
 *  - a database payload (stored in the `notifications` table)
 *  - an email body (sent through the configured mailer)
 *  - a Pusher channel + event name (real-time broadcast)
 *
 * The class is deliberately thin: it only wires the three delivery
 * channels together so each subclass can focus on its own copy.
 */
abstract class BaseNotification implements Notification
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The notification ID (required by Laravel's NotificationSender).
     */
    public ?string $id = null;

    /**
     * The recipient this notification is addressed to.
     * Set by the listener before sending.
     */
    public ?int $recipientId = null;

    public function __construct()
    {
        $this->id ??= (string) \Illuminate\Support\Str::uuid();
    }

    /**
     * Human readable title shown in the notification center and email subject.
     */
    abstract public function title(): string;

    /**
     * One-line body shown in the notification center and email.
     */
    abstract public function body(): string;

    /**
     * Icon name rendered by the <x-ui.avatar> / notification rail.
     */
    public function icon(): string
    {
        return 'bell';
    }

    /**
     * Badge colour for the notification rail.
     */
    public function color(): string
    {
        return 'brand';
    }

    /**
     * Optional deep-link route the notification points at.
     */
    public function actionUrl(): ?string
    {
        return null;
    }

    /**
     * Optional action label shown next to the deep link.
     */
    public function actionLabel(): ?string
    {
        return null;
    }

    /**
     * Delivery channels: database (inbox), mail (email), broadcast (real-time).
     */
    public function via($notifiable): array
    {
        $channels = ['database', 'broadcast'];

        if ($this->shouldEmail($notifiable)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Whether this recipient has opted in to email for this notification type.
     * Defaults to true; the User model exposes per-type preferences.
     */
    protected function shouldEmail($notifiable): bool
    {
        if (! method_exists($notifiable, 'wantsEmail')) {
            return true;
        }

        return $notifiable->wantsEmail($this->emailPreferenceKey());
    }

    /**
     * Preference key used by User::wantsEmail().
     * Defaults to the snake_case class name (e.g. ReservationCreatedNotification
     * -> "reservation_created_notification"). Subclasses override to use a
     * shorter, stable key that survives class renames.
     */
    public function emailPreferenceKey(): string
    {
        return Str::snake(class_basename(static::class));
    }

    /**
     * Database payload stored in the `notifications` table.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => static::class,
            'title' => $this->title(),
            'body' => $this->body(),
            'icon' => $this->icon(),
            'color' => $this->color(),
            'action_url' => $this->actionUrl(),
            'action_label' => $this->actionLabel(),
        ];
    }

    /**
     * Email representation.
     */
    public function toMail($notifiable)
    {
        return (new \App\Mail\NotificationMail(
            mailSubject: $this->title(),
            mailBlade: 'mail.notification',
            mailViewData: [
                'title' => $this->title(),
                'body' => $this->body(),
                'actionUrl' => $this->actionUrl(),
                'actionLabel' => $this->actionLabel(),
                'notifiable' => $notifiable,
            ],
        ))->to($this->emailAddress($notifiable));
    }

    /**
     * Broadcast channel + event name for real-time updates.
     */
    public function broadcastOn($notifiable): array
    {
        return [new PrivateChannel('user.'.$this->recipientId($notifiable))];
    }

    public function broadcastAs(): string
    {
        return class_basename(static::class);
    }

    protected function recipientId($notifiable): int
    {
        return $this->recipientId ?? ($notifiable->id ?? 0);
    }

    protected function emailAddress($notifiable)
    {
        return $notifiable->email ?? config('mail.from.address', 'hello@example.com');
    }

    /**
     * Persist this notification to the database for the given notifiable.
     */
    public function save($notifiable): void
    {
        \App\Models\Notification::create([
            'type' => static::class,
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->id,
            'data' => $this->toArray($notifiable),
        ]);

        // Also send email if applicable
        if ($this->shouldEmail($notifiable)) {
            Mail::to($this->emailAddress($notifiable))->send($this->toMail($notifiable));
        }
    }
}
