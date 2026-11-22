<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vaccine;

/**
 * Only admins manage the vaccine catalog.
 */
class VaccinePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Vaccine $vaccine): bool
    {
        return $user->isAdmin();
    }

    /**
     * Vaccines in the routine schedule can only be deactivated, not deleted.
     */
    public function delete(User $user, Vaccine $vaccine): bool
    {
        return $user->isAdmin() && ! $vaccine->isScheduled();
    }
}
