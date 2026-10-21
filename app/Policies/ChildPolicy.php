<?php

namespace App\Policies;

use App\Models\Child;
use App\Models\User;

/**
 * A parent can only see and change their own children.
 */
class ChildPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isParent();
    }

    public function view(User $user, Child $child): bool
    {
        return $this->owns($user, $child);
    }

    public function create(User $user): bool
    {
        return $user->isParent();
    }

    public function update(User $user, Child $child): bool
    {
        return $this->owns($user, $child);
    }

    public function delete(User $user, Child $child): bool
    {
        return $this->owns($user, $child);
    }

    /**
     * Book appointments for this child.
     */
    public function book(User $user, Child $child): bool
    {
        return $this->owns($user, $child);
    }

    private function owns(User $user, Child $child): bool
    {
        return $user->isParent() && $child->parent_id === $user->id;
    }
}
