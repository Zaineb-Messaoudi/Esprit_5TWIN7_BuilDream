<?php

namespace App\Http\Controllers\Api;

use App\Support\NotificationPreferenceCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Authenticated user profile + preferences, exposed over the API.
 *
 * The JSON shape is intentionally stable: a mobile or SPA client can read
 * `user`, `notification_preferences` and `unread_count` in a single call.
 */
class AccountController
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user()->load('equipment.category');

        $preferences = [];
        foreach (NotificationPreferenceCatalog::all() as $pref) {
            $preferences[$pref['key']] = $user->wantsEmail($pref['key']);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_admin' => $user->isAdmin(),
                'is_owner' => $user->isOwner(),
                'is_buyer' => $user->isBuyer(),
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'equipment_count' => $user->equipment->count(),
                'created_at' => $user->created_at?->toIso8601String(),
            ],
            'notification_preferences' => $preferences,
            'unread_notifications' => $user->unreadNotifications()->count(),
        ]);
    }
}
