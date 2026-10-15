<?php

use App\Models\Delivery;
use App\Models\Equipment;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given array will be used to authorize the
| user's access to the given channel.
|
*/

Broadcast::channel('user.{userId}', function (User $user, int $userId) {
    return (int) $user->id === $userId;
});

Broadcast::channel('rental.{rentalId}', function (User $user, int $rentalId) {
    return Rental::where('id', $rentalId)
        ->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhereHas('equipment', fn ($q) => $q->where('owner_id', $user->id));
        })
        ->exists();
});

Broadcast::channel('delivery.{deliveryId}', function (User $user, int $deliveryId) {
    return Delivery::where('id', $deliveryId)
        ->where(function ($q) use ($user) {
            $q->where('owner_id', $user->id)
                ->orWhere('renter_id', $user->id);
        })
        ->exists();
});

Broadcast::channel('equipment.{equipmentId}', function (User $user, int $equipmentId) {
    return Equipment::where('id', $equipmentId)
        ->where('owner_id', $user->id)
        ->exists();
});

Broadcast::channel('admin.notifications', function (User $user) {
    return $user->isAdmin();
});
