<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): Response
    {
        if ($user->role === UserRole::ADMIN) {
            return Response::allow();
        }

        return $user->id === $model->id
            ? Response::allow()
            : Response::deny('You do not own this profile.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): Response
    {
        if ($user->role === UserRole::ADMIN) {
            return Response::allow();
        }

        return $user->id === $model->id
            ? Response::allow()
            : Response::deny('You can only update your own profile.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): Response
    {
        if ($user->role === UserRole::ADMIN) {
            return Response::allow();
        }

        return $user->id === $model->id
            ? Response::allow()
            : Response::deny('You can only delete your own account.');
    }
}
