<?php

namespace App\Policies;

use App\Models\User;

class ActivityLogPolicy
{
    public function viewActivity(User $user): bool
    {
        return $user->isAdmin();
    }
}
