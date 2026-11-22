<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Child;
use App\Models\Hospital;
use App\Models\HospitalStock;
use App\Models\TimeSlot;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * What a parent can choose at each step of booking: doses, then hospitals, then slots.
 * These are read-only lists for display; AppointmentService re-checks everything
 * under locks when the booking is confirmed.
 */
final class BookingOptions
{
    private VaccinationSchedule $schedule;

    public function __construct(private readonly Child $child)
    {
        $this->schedule = $child->schedule();
    }

    public function schedule(): VaccinationSchedule
    {
        return $this->schedule;
    }

    /**
     * Doses that can be booked now, each with its open appointment if it already has one.
     *
     * @return Collection<int, array{dose: ScheduledDose, appointment: Appointment|null}>
     */
    public function bookableDoses(): Collection
    {
        $open = Appointment::open()
            ->where('child_id', $this->child->id)
            ->with('timeSlot', 'hospital')
            ->get()
            ->keyBy('child_dose_id');

        return $this->schedule->doses()
            ->filter(fn (ScheduledDose $d) => $d->isBookable())
            ->map(fn (ScheduledDose $d) => ['dose' => $d, 'appointment' => $open->get($d->dose->id)])
            ->values();
    }

    /**
     * The chosen doses, validated: bookable and without an open appointment.
     *
     * @param  array<int|string>  $childDoseIds
     * @return Collection<int, ScheduledDose>
     */
    public function selected(array $childDoseIds): Collection
    {
        $ids = array_map('intval', $childDoseIds);

        return $this->bookableDoses()
            ->filter(fn (array $row) => $row['appointment'] === null && in_array($row['dose']->dose->id, $ids, true))
            ->pluck('dose')
            ->values();
    }

    /**
     * Dates on which every chosen dose may be given: [from, to]; to is null when open-ended.
     *
     * @param  Collection<int, ScheduledDose>  $doses
     * @return array{0: CarbonImmutable, 1: CarbonImmutable|null}
     */
    public function window(Collection $doses): array
    {
        $from = $doses->map(fn (ScheduledDose $d) => $d->effectiveDueDate)
            ->push(CarbonImmutable::today())
            ->max();

        $to = $doses->map(fn (ScheduledDose $d) => $d->lastDate)->filter()->min();

        return [$from, $to];
    }

    /**
     * Active hospitals with usable stock of every chosen vaccine and at least one free slot
     * in the window. Each comes with `next_slot_date`.
     *
     * @param  Collection<int, ScheduledDose>  $doses
     * @return Collection<int, Hospital>
     */
    public function hospitals(Collection $doses, ?string $search = null): Collection
    {
        [$from, $to] = $this->window($doses);

        if ($to !== null && $to->lt($from)) {
            return collect();
        }

        $vaccineIds = $doses->map(fn (ScheduledDose $d) => $d->schedule()->vaccine_id)->unique();

        $query = Hospital::active()
            ->when($search, fn (Builder $q) => $q->where(fn (Builder $q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")));

        foreach ($vaccineIds as $vaccineId) {
            $query->whereHas('stock', fn (Builder $q) => $q
                ->where('vaccine_id', $vaccineId)
                ->usableOn($from)
                ->whereColumn('quantity', '>', 'reserved'));
        }

        return $query->orderBy('city')->orderBy('name')->get()
            ->map(function (Hospital $hospital) use ($doses) {
                $hospital->setAttribute('next_slot_date', $this->slotQuery($hospital, $doses)?->min('date'));

                return $hospital;
            })
            ->filter(fn (Hospital $hospital) => $hospital->next_slot_date !== null)
            ->values();
    }

    /**
     * Free slots at the hospital, starting at least the minimum notice from now, on which
     * every chosen dose can be given and every vaccine is still in stock (a batch
     * lasting until that date).
     *
     * @param  Collection<int, ScheduledDose>  $doses
     * @return Collection<int, TimeSlot>
     */
    public function slots(Hospital $hospital, Collection $doses, int $limit = 120): Collection
    {
        return $this->slotQuery($hospital, $doses)
            ?->withBookedChildren()
            ->orderBy('date')
            ->orderBy('start_time')
            ->limit($limit)
            ->get() ?? collect();
    }

    /**
     * @param  Collection<int, ScheduledDose>  $doses
     * @return Builder<TimeSlot>|null null when no date works
     */
    private function slotQuery(Hospital $hospital, Collection $doses): ?Builder
    {
        [$from, $to] = $this->window($doses);

        // The last date each vaccine is still in stock: the latest expiry among batches with doses left.
        foreach ($doses->map(fn (ScheduledDose $d) => $d->schedule()->vaccine_id)->unique() as $vaccineId) {
            $lastInStock = HospitalStock::where('hospital_id', $hospital->id)
                ->where('vaccine_id', $vaccineId)
                ->whereColumn('quantity', '>', 'reserved')
                ->max('expiry_date');

            if ($lastInStock === null) {
                return null;
            }

            $lastInStock = CarbonImmutable::parse($lastInStock);
            $to = $to === null ? $lastInStock : $to->min($lastInStock);
        }

        if ($to->lt($from)) {
            return null;
        }

        return TimeSlot::query()
            ->select('time_slots.*')
            ->where('hospital_id', $hospital->id)
            ->whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString())
            ->startsAfter(CarbonImmutable::now()->addMinutes(config('vaccination.booking.min_notice_minutes')))
            ->withFreePlaces();
    }
}
