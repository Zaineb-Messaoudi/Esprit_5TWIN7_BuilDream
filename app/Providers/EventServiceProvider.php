<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        \App\Events\ReservationCreated::class => [
            \App\Listeners\SendReservationCreatedNotification::class,
        ],
        \App\Events\ReservationApproved::class => [
            \App\Listeners\SendReservationApprovedNotification::class,
        ],
        \App\Events\ReservationRejected::class => [
            \App\Listeners\SendReservationRejectedNotification::class,
        ],
        \App\Events\PaymentReceived::class => [
            \App\Listeners\SendPaymentReceivedNotification::class,
        ],
        \App\Events\RentalStarted::class => [
            \App\Listeners\SendRentalStartedNotification::class,
        ],
        \App\Events\EquipmentReturned::class => [
            \App\Listeners\SendEquipmentReturnedNotification::class,
        ],
        \App\Events\ExtensionRequested::class => [
            \App\Listeners\SendExtensionRequestedNotification::class,
        ],
        \App\Events\ExtensionApproved::class => [
            \App\Listeners\SendExtensionApprovedNotification::class,
        ],
        \App\Events\ExtensionRejected::class => [
            \App\Listeners\SendExtensionRejectedNotification::class,
        ],
        \App\Events\MaintenanceCreated::class => [
            \App\Listeners\SendMaintenanceCreatedNotification::class,
        ],
        \App\Events\MaintenanceCompleted::class => [
            \App\Listeners\SendMaintenanceCompletedNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
