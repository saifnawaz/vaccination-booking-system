<?php

namespace App\Policies;

use App\Models\HospitalStock;
use App\Models\User;

/**
 * A hospital can only see and change its own batches.
 */
class HospitalStockPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isHospital();
    }

    public function create(User $user): bool
    {
        return $user->isHospital();
    }

    public function update(User $user, HospitalStock $batch): bool
    {
        return $this->owns($user, $batch);
    }

    public function delete(User $user, HospitalStock $batch): bool
    {
        return $this->owns($user, $batch);
    }

    private function owns(User $user, HospitalStock $batch): bool
    {
        return $user->isHospital() && $batch->hospital_id === $user->hospital?->id;
    }
}
