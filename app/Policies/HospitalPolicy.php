<?php

namespace App\Policies;

use App\Models\Hospital;
use App\Models\User;

class HospitalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Hospital $hospital): bool
    {
        return $user->isAdmin() || $this->owns($user, $hospital);
    }

    /**
     * Editing the hospital profile is for the hospital itself, not admins.
     */
    public function update(User $user, Hospital $hospital): bool
    {
        return $this->owns($user, $hospital);
    }

    /**
     * Approve, decline, deactivate or reactivate.
     */
    public function changeStatus(User $user, Hospital $hospital): bool
    {
        return $user->isAdmin();
    }

    /**
     * Soft delete (refused while appointments are open; see Admin\HospitalController).
     */
    public function delete(User $user, Hospital $hospital): bool
    {
        return $user->isAdmin();
    }

    private function owns(User $user, Hospital $hospital): bool
    {
        return $user->isHospital() && $hospital->user_id === $user->id;
    }
}
