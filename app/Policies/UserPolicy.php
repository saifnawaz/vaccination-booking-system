<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Listing and creating admin accounts. Admins are only ever created
     * by another admin or by the seeder, never through public signup.
     */
    public function manageAdmins(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Self-service account deletion is for parents only. Hospitals are
     * deactivated by an admin, and admins are not removed from the UI.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->isParent() && $user->is($model);
    }
}
