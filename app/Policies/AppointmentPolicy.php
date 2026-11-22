<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

/**
 * Parents act on their own children's appointments; hospitals on appointments at
 * their own hospital. Admins can look but not change. (Booking for a child is
 * ChildPolicy::book.)
 */
class AppointmentPolicy
{
    public function view(User $user, Appointment $appointment): bool
    {
        return $user->isAdmin()
            || $this->parentOwns($user, $appointment)
            || $this->hospitalOwns($user, $appointment);
    }

    /**
     * Cancel or reschedule (time limits are enforced by AppointmentService).
     */
    public function change(User $user, Appointment $appointment): bool
    {
        return $this->parentOwns($user, $appointment);
    }

    /**
     * Approve, reject, complete, mark no-show, record earlier doses for the child.
     */
    public function manage(User $user, Appointment $appointment): bool
    {
        return $this->hospitalOwns($user, $appointment);
    }

    private function parentOwns(User $user, Appointment $appointment): bool
    {
        return $user->isParent() && $appointment->child?->parent_id === $user->id;
    }

    private function hospitalOwns(User $user, Appointment $appointment): bool
    {
        return $user->isHospital() && $appointment->hospital_id === $user->hospital?->id;
    }
}
