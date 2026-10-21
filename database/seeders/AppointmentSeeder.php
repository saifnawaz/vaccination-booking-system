<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\HospitalStatus;
use App\Models\Appointment;
use App\Models\Child;
use App\Models\ChildDose;
use App\Models\Hospital;
use App\Models\TimeSlot;
use App\Services\AppointmentService;
use App\Services\ScheduledDose;
use Illuminate\Database\Seeder;

/**
 * Demo appointments in every status.
 *
 * Upcoming ones are booked through AppointmentService so stock reservations are
 * real. Past ones (completed, no-show) are written directly, since the service
 * rightly refuses to book in the past; they reuse doses ChildSeeder marked as given.
 */
class AppointmentSeeder extends Seeder
{
    public function run(AppointmentService $service): void
    {
        [$first, $second] = Hospital::where('status', HospitalStatus::Active)->orderBy('id')->take(2)->get();

        // Confirmed: Noor's 6-week vaccines.
        $service->book($this->child('Noor'), $this->needing('Noor'), $this->slot($first, 3))
            ->each(fn (Appointment $a) => $service->approve($a));

        // Awaiting confirmation: Amna's 10-week vaccines, Ahmed's 9-month vaccines.
        $service->book($this->child('Amna'), $this->needing('Amna'), $this->slot($second, 4));
        $service->book($this->child('Ahmed'), $this->needing('Ahmed'), $this->slot($first, 5));

        // Cancelled by the parent: Laiba's overdue BCG.
        $service->book($this->child('Laiba'), $this->needing('Laiba', ['BCG']), $this->slot($first, 2))
            ->each(fn (Appointment $a) => $service->cancel($a, 'We are travelling that week.'));

        // Declined by the hospital: Khadija's MR-1.
        $service->book($this->child('Khadija'), $this->needing('Khadija', ['MR-1']), $this->slot($second, 6))
            ->each(fn (Appointment $a) => $service->reject($a, 'Our vaccinator is on leave that day. Please choose another date.'));

        // Completed in the past: the 10-week visit ChildSeeder recorded for Saad.
        $this->pastVisit($this->child('Saad'), ['OPV-2', 'Pentavalent-2', 'PCV-2', 'Rota-2'], AppointmentStatus::Completed);

        // Didn't come: Ibrahim's overdue Pentavalent-1, eight days ago.
        $this->pastVisit($this->child('Ibrahim'), ['Pentavalent-1'], AppointmentStatus::NoShow, $first, today()->subDays(8));
    }

    private function child(string $firstName): Child
    {
        return Child::where('first_name', $firstName)->sole();
    }

    /**
     * Ids of the child's doses that are due or overdue now, optionally only these labels.
     *
     * @param  list<string>|null  $labels
     * @return list<int>
     */
    private function needing(string $firstName, ?array $labels = null): array
    {
        return $this->child($firstName)->schedule()->doses()
            ->filter(fn (ScheduledDose $d) => $d->status->needsAction())
            ->filter(fn (ScheduledDose $d) => $labels === null || in_array($d->label(), $labels, true))
            ->map(fn (ScheduledDose $d) => $d->dose->id)
            ->values()
            ->all();
    }

    /** The first slot at least $days days from today. */
    private function slot(Hospital $hospital, int $days): TimeSlot
    {
        return $hospital->timeSlots()
            ->whereDate('date', '>=', today()->addDays($days))
            ->orderBy('date')->orderBy('start_time')
            ->firstOrFail();
    }

    /**
     * A finished visit. Completed visits use doses already recorded as given (and their
     * batch); a no-show uses a batch of the dose's vaccine, with nothing reserved.
     *
     * @param  list<string>  $labels
     */
    private function pastVisit(Child $child, array $labels, AppointmentStatus $status, ?Hospital $hospital = null, $date = null): void
    {
        $doses = $child->doses()->with('scheduleDose', 'hospitalStock')
            ->whereHas('scheduleDose', fn ($q) => $q->whereIn('label', $labels))
            ->get();

        $date ??= $doses->first()->administered_at;
        $hospitalId = $hospital?->id ?? $doses->first()->administered_by_hospital_id;

        $slot = new TimeSlot(['date' => $date, 'start_time' => '09:00', 'end_time' => '09:30', 'capacity' => 4]);
        $slot->hospital_id = $hospitalId;
        $slot->save();

        $doses->each(function (ChildDose $dose) use ($child, $slot, $status, $hospitalId) {
            $batchId = $dose->hospital_stock_id
                ?? Hospital::find($hospitalId)->stock()->where('vaccine_id', $dose->scheduleDose->vaccine_id)->orderByDesc('expiry_date')->value('id');

            $appointment = new Appointment;
            $appointment->child_id = $child->id;
            $appointment->child_dose_id = $dose->id;
            $appointment->hospital_id = $hospitalId;
            $appointment->time_slot_id = $slot->id;
            $appointment->hospital_stock_id = $batchId;
            $appointment->status = $status;
            $appointment->approved_at = $slot->date->subDays(3);
            $appointment->completed_at = $status === AppointmentStatus::Completed ? $slot->date->setTime(9, 15) : null;
            $appointment->no_show_at = $status === AppointmentStatus::NoShow ? $slot->date->setTime(12, 0) : null;
            $appointment->created_at = $slot->date->subDays(5);
            $appointment->save();
        });
    }
}
