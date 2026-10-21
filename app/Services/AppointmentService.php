<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\DoseSource;
use App\Exceptions\BookingException;
use App\Models\Appointment;
use App\Models\Child;
use App\Models\ChildDose;
use App\Models\Hospital;
use App\Models\HospitalStock;
use App\Models\TimeSlot;
use App\Models\Vaccine;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Every change to appointments goes through here, so stock and slots stay consistent.
 *
 * Each method runs in one database transaction and locks the rows it reads before
 * deciding anything (SELECT … FOR UPDATE), so two requests can never both take the
 * last dose or the last place in a slot. Locks are always taken in the same order:
 *   child doses → appointment → time slot → stock batches
 * which avoids deadlocks; the transaction is retried if MySQL still reports one.
 *
 * Every check that decides something is a locking read too, even counts. Under
 * MySQL's default REPEATABLE READ a plain SELECT reads the transaction's snapshot,
 * which can be older than a booking another request committed while we waited for
 * a lock; a locking read always sees the latest committed rows.
 *
 * Stock: booking reserves one dose (reserved + 1) from the usable batch that expires
 * soonest; reject, cancel and no-show give it back (reserved - 1); completing uses it
 * up (quantity - 1 and reserved - 1). A batch is usable on a date if it expires on or
 * after that date and has quantity > reserved.
 *
 * Breaking a rule throws BookingException with a message for the user.
 */
final class AppointmentService
{
    private const ATTEMPTS = 3;

    /**
     * Book one or more of a child's doses into one slot (one visit).
     * All or nothing: if any dose can't be booked, none are.
     *
     * @param  list<int>  $childDoseIds
     * @return Collection<int, Appointment>
     */
    public function book(Child $child, array $childDoseIds, TimeSlot $slot): Collection
    {
        $ids = collect($childDoseIds)->map(fn ($id) => (int) $id)->unique()->sort()->values();

        if ($ids->isEmpty()) {
            throw new BookingException('Choose at least one vaccine to book.');
        }

        try {
            return DB::transaction(function () use ($child, $ids, $slot) {
                $doses = ChildDose::query()
                    ->whereIn('id', $ids)
                    ->where('child_id', $child->id)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                if ($doses->count() !== $ids->count()) {
                    throw new BookingException('One of the chosen vaccines is not on this child\'s schedule.');
                }

                $this->ensureNoOpenAppointment($doses);

                $slot = $this->lockSlot($slot->id);
                $this->ensureSlotTakesChild($slot, $child->id);
                $this->ensureDosesCanBeGivenOn($child, $doses, $slot->date);

                return $doses
                    ->load('scheduleDose.vaccine')
                    ->sortBy(fn (ChildDose $dose) => $dose->scheduleDose->vaccine_id)
                    ->map(function (ChildDose $dose) use ($child, $slot) {
                        $batch = $this->lockUsableBatch($slot->hospital_id, $dose->scheduleDose->vaccine, $slot->date);
                        $batch->reserved++;
                        $batch->save();

                        $appointment = new Appointment;
                        $appointment->child_id = $child->id;
                        $appointment->child_dose_id = $dose->id;
                        $appointment->hospital_id = $slot->hospital_id;
                        $appointment->time_slot_id = $slot->id;
                        $appointment->hospital_stock_id = $batch->id;
                        $appointment->status = AppointmentStatus::Pending;
                        $appointment->save();

                        return $appointment;
                    })
                    ->values();
            }, self::ATTEMPTS);
        } catch (UniqueConstraintViolationException) {
            // The database backstop: another request booked one of these doses first.
            throw new BookingException('One of these vaccines has just been booked. Please check your appointments.');
        }
    }

    /**
     * Parent cancels, up to the cut-off before the slot starts.
     */
    public function cancel(Appointment $appointment, ?string $reason = null): Appointment
    {
        return DB::transaction(function () use ($appointment, $reason) {
            $appointment = $this->lockAppointment($appointment->id);
            $this->ensureParentCanChange($appointment);
            $this->transition($appointment, AppointmentStatus::Cancelled);

            $this->releaseReservation($appointment);
            $appointment->reason = $reason;
            $appointment->cancelled_at = now();
            $appointment->save();

            return $appointment;
        }, self::ATTEMPTS);
    }

    /**
     * Parent moves an open appointment to another slot at the same hospital, up to the
     * cut-off. It goes back to pending for the hospital to confirm. If the reserved
     * batch expires before the new date, the reservation moves to another usable
     * batch; if there is none, the new date is refused.
     */
    public function reschedule(Appointment $appointment, TimeSlot $newSlot): Appointment
    {
        return DB::transaction(function () use ($appointment, $newSlot) {
            $appointment = $this->lockAppointment($appointment->id);
            $this->ensureParentCanChange($appointment);

            if ($newSlot->id === $appointment->time_slot_id) {
                throw new BookingException('That is already the time of this appointment.');
            }

            if ($newSlot->hospital_id !== $appointment->hospital_id) {
                throw new BookingException('An appointment can only be moved to another time at the same hospital.');
            }

            $slot = $this->lockSlot($newSlot->id);
            $this->ensureSlotTakesChild($slot, $appointment->child_id);
            $this->ensureDosesCanBeGivenOn($appointment->child, collect([$appointment->childDose]), $slot->date);

            $current = $this->lockBatch($appointment->hospital_stock_id);

            if ($current->expiry_date->lt($slot->date)) {
                $replacement = $this->lockUsableBatch($slot->hospital_id, $current->vaccine, $slot->date, exceptBatchId: $current->id);

                $this->decrementReserved($current);
                $replacement->reserved++;
                $replacement->save();

                $appointment->hospital_stock_id = $replacement->id;
            }

            $appointment->time_slot_id = $slot->id;
            $appointment->status = AppointmentStatus::Pending;
            $appointment->approved_at = null;
            $appointment->save();

            return $appointment;
        }, self::ATTEMPTS);
    }

    public function approve(Appointment $appointment): Appointment
    {
        return DB::transaction(function () use ($appointment) {
            $appointment = $this->lockAppointment($appointment->id);
            $this->transition($appointment, AppointmentStatus::Approved);

            $appointment->approved_at = now();
            $appointment->save();

            return $appointment;
        }, self::ATTEMPTS);
    }

    public function reject(Appointment $appointment, string $reason): Appointment
    {
        return DB::transaction(function () use ($appointment, $reason) {
            $appointment = $this->lockAppointment($appointment->id);
            $this->transition($appointment, AppointmentStatus::Rejected);

            $this->releaseReservation($appointment);
            $appointment->reason = $reason;
            $appointment->rejected_at = now();
            $appointment->save();

            return $appointment;
        }, self::ATTEMPTS);
    }

    /**
     * The dose was given: use up the reserved dose and record it on the child's schedule.
     */
    public function complete(Appointment $appointment): Appointment
    {
        return DB::transaction(function () use ($appointment) {
            $dose = ChildDose::whereKey($appointment->child_dose_id)->lockForUpdate()->firstOrFail();
            $appointment = $this->lockAppointment($appointment->id);
            $this->transition($appointment, AppointmentStatus::Completed);

            $date = $appointment->timeSlot->date;

            if ($date->isAfter(CarbonImmutable::today())) {
                throw new BookingException('An appointment can only be completed on or after its date.');
            }

            if ($dose->isAdministered()) {
                throw new BookingException('This dose is already recorded as given.');
            }

            $batch = $this->lockBatch($appointment->hospital_stock_id);
            if ($batch->quantity < 1) {
                throw new LogicException("Batch {$batch->id} has a reservation but no doses.");
            }
            $batch->quantity--;
            $this->decrementReserved($batch);

            $dose->administered_at = $date;
            $dose->administered_by_hospital_id = $appointment->hospital_id;
            $dose->hospital_stock_id = $batch->id;
            $dose->batch_number = $batch->batch_number;
            $dose->source = DoseSource::GivenHere;
            $dose->save();

            $appointment->completed_at = now();
            $appointment->save();

            return $appointment;
        }, self::ATTEMPTS);
    }

    /**
     * The child didn't come: release the reserved dose. Only after the slot has started.
     */
    public function markNoShow(Appointment $appointment): Appointment
    {
        return DB::transaction(function () use ($appointment) {
            $appointment = $this->lockAppointment($appointment->id);
            $this->transition($appointment, AppointmentStatus::NoShow);

            if (! $appointment->timeSlot->hasStarted()) {
                throw new BookingException('An appointment can only be marked as missed once its time has started.');
            }

            $this->releaseReservation($appointment);
            $appointment->no_show_at = now();
            $appointment->save();

            return $appointment;
        }, self::ATTEMPTS);
    }

    /**
     * A hospital copies a dose from the child's previous records (e.g. a vaccination
     * card). No stock is used. Doses must be recorded in order within a series.
     */
    public function recordHistoricalDose(ChildDose $dose, Hospital $hospital, CarbonImmutable $givenOn, ?string $batchNumber): ChildDose
    {
        return DB::transaction(function () use ($dose, $hospital, $givenOn, $batchNumber) {
            $dose = ChildDose::whereKey($dose->id)->lockForUpdate()->firstOrFail();
            $dose->load('scheduleDose', 'child');
            $child = $dose->child;
            $schedule = $dose->scheduleDose;

            if ($dose->isAdministered()) {
                throw new BookingException("{$schedule->label} is already recorded.");
            }

            if ($dose->appointments()->open()->exists()) {
                throw new BookingException("{$schedule->label} has an open appointment. Complete or cancel that appointment instead.");
            }

            if ($givenOn->lt($child->date_of_birth) || $givenOn->isAfter(CarbonImmutable::today())) {
                throw new BookingException('The date given must be between the date of birth and today.');
            }

            if ($schedule->max_age_days !== null && $givenOn->gt($child->date_of_birth->addDays($schedule->max_age_days))) {
                throw new BookingException("{$schedule->label} can't be given after ".$child->date_of_birth->addDays($schedule->max_age_days)->format('j M Y').'. Check the date.');
            }

            if ($schedule->dose_number >= 1) {
                $series = $child->doses()
                    ->whereHas('scheduleDose', fn ($q) => $q->where('vaccine_id', $schedule->vaccine_id)->where('dose_number', '>=', 1))
                    ->with('scheduleDose')
                    ->get()
                    ->keyBy(fn (ChildDose $d) => $d->scheduleDose->dose_number);

                $previous = $series->get($schedule->dose_number - 1);
                if ($previous && ! $previous->isAdministered()) {
                    throw new BookingException("Record {$previous->scheduleDose->label} first.");
                }
                if ($previous && $givenOn->lt($previous->administered_at)) {
                    throw new BookingException("{$schedule->label} can't be earlier than {$previous->scheduleDose->label} (".$previous->administered_at->format('j M Y').').');
                }

                $next = $series->get($schedule->dose_number + 1);
                if ($next?->isAdministered() && $givenOn->gt($next->administered_at)) {
                    throw new BookingException("{$schedule->label} can't be later than {$next->scheduleDose->label} (".$next->administered_at->format('j M Y').').');
                }
            }

            $dose->administered_at = $givenOn;
            $dose->administered_by_hospital_id = $hospital->id;
            $dose->hospital_stock_id = null;
            $dose->batch_number = $batchNumber ?: null;
            $dose->source = DoseSource::HistoricalRecord;
            $dose->save();

            return $dose;
        }, self::ATTEMPTS);
    }

    /**
     * @param  Collection<int, ChildDose>  $doses
     */
    private function ensureNoOpenAppointment(Collection $doses): void
    {
        $taken = Appointment::open()->whereIn('child_dose_id', $doses->modelKeys())->lockForUpdate()->with('childDose.scheduleDose')->first();

        if ($taken) {
            throw new BookingException("{$taken->doseLabel()} already has an appointment. Cancel or reschedule that one instead.");
        }
    }

    /**
     * The slot is bookable and has room for this child. A child who already has an
     * open appointment in the slot (another vaccine) doesn't take another place.
     */
    private function ensureSlotTakesChild(TimeSlot $slot, int $childId): void
    {
        $hospital = Hospital::find($slot->hospital_id);

        if (! $hospital?->isActive()) {
            throw new BookingException('This hospital is not taking bookings at the moment.');
        }

        if ($slot->hasStarted()) {
            throw new BookingException('That time has already passed. Please choose another.');
        }

        // Locking reads: see bookings committed while we waited for the slot lock.
        $open = $slot->appointments()->open()->lockForUpdate();
        $alreadyIn = (clone $open)->where('child_id', $childId)->value('id') !== null;
        $booked = (clone $open)->distinct()->count('child_id');

        if (! $alreadyIn && $booked >= $slot->capacity) {
            throw new BookingException('That time is now fully booked. Please choose another.');
        }
    }

    /**
     * @param  Collection<int, ChildDose>  $doses
     */
    private function ensureDosesCanBeGivenOn(Child $child, Collection $doses, CarbonImmutable $date): void
    {
        $schedule = Child::withTrashed()->findOrFail($child->id)->schedule();

        foreach ($doses as $dose) {
            $scheduled = $schedule->doses()->first(fn (ScheduledDose $d) => $d->dose->id === $dose->id);

            if (! $scheduled?->isBookable()) {
                throw new BookingException(($scheduled?->label() ?? 'This vaccine').' can\'t be booked: '.lcfirst($scheduled?->summary() ?? 'it is not on the schedule').'.');
            }

            if (! $scheduled->canBeGivenOn($date)) {
                $window = $scheduled->lastDate
                    ? 'between '.$scheduled->effectiveDueDate->format('j M Y').' and '.$scheduled->lastDate->format('j M Y')
                    : 'on or after '.$scheduled->effectiveDueDate->format('j M Y');

                throw new BookingException("{$scheduled->label()} can only be given {$window}.");
            }
        }
    }

    private function ensureParentCanChange(Appointment $appointment): void
    {
        if (! $appointment->isOpen()) {
            throw new BookingException('This appointment is '.strtolower($appointment->status->label()).' and can no longer be changed.');
        }

        if (! $appointment->canBeChangedByParent()) {
            $hours = config('vaccination.booking.change_cutoff_hours');

            throw new BookingException("Appointments can only be changed online up to {$hours} hours before. Please contact the hospital.");
        }
    }

    private function transition(Appointment $appointment, AppointmentStatus $next): void
    {
        if (! $appointment->status->canTransitionTo($next)) {
            throw new BookingException('This appointment is '.strtolower($appointment->status->label()).', so it can\'t be changed to '.strtolower($next->label()).'.');
        }

        $appointment->status = $next;
    }

    private function releaseReservation(Appointment $appointment): void
    {
        $this->decrementReserved($this->lockBatch($appointment->hospital_stock_id));
    }

    private function decrementReserved(HospitalStock $batch): void
    {
        if ($batch->reserved < 1) {
            throw new LogicException("Batch {$batch->id} has no reservation to release.");
        }

        $batch->reserved--;
        $batch->save();
    }

    private function lockAppointment(int $id): Appointment
    {
        return Appointment::whereKey($id)->lockForUpdate()->with('timeSlot', 'childDose.scheduleDose', 'child')->firstOrFail();
    }

    private function lockSlot(int $id): TimeSlot
    {
        return TimeSlot::whereKey($id)->lockForUpdate()->firstOrFail();
    }

    private function lockBatch(int $id): HospitalStock
    {
        return HospitalStock::whereKey($id)->lockForUpdate()->with('vaccine')->firstOrFail();
    }

    /**
     * The usable batch that expires soonest, locked. Never an expired one.
     */
    private function lockUsableBatch(int $hospitalId, Vaccine $vaccine, CarbonImmutable $date, ?int $exceptBatchId = null): HospitalStock
    {
        if (! $vaccine->is_active || $vaccine->trashed()) {
            throw new BookingException("{$vaccine->name} is not available for booking at the moment.");
        }

        $batch = HospitalStock::query()
            ->where('hospital_id', $hospitalId)
            ->where('vaccine_id', $vaccine->id)
            ->usableOn($date)
            ->whereColumn('quantity', '>', 'reserved')
            ->when($exceptBatchId, fn ($q) => $q->whereKeyNot($exceptBatchId))
            ->orderBy('expiry_date')
            ->orderBy('id')
            ->lockForUpdate()
            ->first();

        if (! $batch) {
            throw new BookingException("{$vaccine->name} is not in stock at this hospital for ".$date->format('j M Y').'. Please choose another date or hospital.');
        }

        return $batch;
    }
}
