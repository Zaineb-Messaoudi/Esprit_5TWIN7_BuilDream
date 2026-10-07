<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast channels
|--------------------------------------------------------------------------
| Every real-time event is sent on a private channel that belongs to one user:
|   owner.{id} - notifications for equipment owners (new reservation, payment, ...)
|   user.{id}  - notifications for buyers (reservation decision, extension, ...)
*/

Broadcast::channel('owner.{id}', fn ($user, $id) => (int) $user->id === (int) $id && $user->isOwner());

Broadcast::channel('user.{id}', fn ($user, $id) => (int) $user->id === (int) $id);
