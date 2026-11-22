<?php

namespace App\Services;

use App\Models\Child;
use App\Models\ChildDose;
use App\Models\Vaccine;
use App\Models\VaccineSchedule;

/**
 * Creates and maintains the child_doses rows for a child.
 */
final class ScheduleGenerator
{
    /**
     * Create one dose row per scheduled dose of every vaccine that has not been
     * deleted from the catalog. Safe to call again: existing rows are kept.
     */
    public function generate(Child $child): void
    {
        $existing = $child->doses()->pluck('vaccine_schedule_id')->all();

        VaccineSchedule::query()
            // Not whereHas('vaccine'): that relation includes trashed vaccines on purpose.
            ->whereIn('vaccine_id', Vaccine::query()->select('id'))
            ->whereNotIn('id', $existing)
            ->get()
            ->each(function (VaccineSchedule $schedule) use ($child) {
                $dose = new ChildDose;
                $dose->child_id = $child->id;
                $dose->vaccine_schedule_id = $schedule->id;
                $dose->due_date = $child->date_of_birth->addDays($schedule->age_days);
                $dose->save();
            });

        $child->unsetRelation('doses');
    }

    /**
     * After a date of birth correction, move every dose's age-based due date.
     */
    public function recalculateDueDates(Child $child): void
    {
        $child->doses()->with('scheduleDose')->get()->each(function (ChildDose $dose) use ($child) {
            $dose->due_date = $child->date_of_birth->addDays($dose->scheduleDose->age_days);
            $dose->save();
        });

        $child->unsetRelation('doses');
    }
}
