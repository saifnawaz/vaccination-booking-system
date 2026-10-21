<?php

namespace App\Models;

use Database\Factories\ChildDoseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A child's copy of one scheduled dose. Nothing here is mass-assignable:
 * rows are created by App\Services\ScheduleGenerator and completed by the
 * hospital workflow, never filled from request input.
 */
class ChildDose extends Model
{
    /** @use HasFactory<ChildDoseFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'due_date' => 'immutable_date',
            'administered_at' => 'immutable_date',
        ];
    }

    /**
     * @return BelongsTo<Child, $this>
     */
    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    /**
     * @return BelongsTo<VaccineSchedule, $this>
     */
    public function scheduleDose(): BelongsTo
    {
        return $this->belongsTo(VaccineSchedule::class, 'vaccine_schedule_id');
    }

    /**
     * The batch the dose was given from.
     *
     * @return BelongsTo<HospitalStock, $this>
     */
    public function hospitalStock(): BelongsTo
    {
        return $this->belongsTo(HospitalStock::class);
    }

    public function isAdministered(): bool
    {
        return $this->administered_at !== null;
    }
}
