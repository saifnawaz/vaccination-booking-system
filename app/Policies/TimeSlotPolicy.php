<?php

namespace App\Policies;

use App\Models\TimeSlot;
use App\Models\User;

/**
 * A hospital can only see and change its own time slots.
 */
class TimeSlotPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isHospital();
    }

    public function create(User $user): bool
    {
        return $user->isHospital();
    }

    public function update(User $user, TimeSlot $slot): bool
    {
        return $this->owns($user, $slot);
    }

    public function delete(User $user, TimeSlot $slot): bool
    {
        return $this->owns($user, $slot);
    }

    private function owns(User $user, TimeSlot $slot): bool
    {
        return $user->isHospital() && $slot->hospital_id === $user->hospital?->id;
    }
}
