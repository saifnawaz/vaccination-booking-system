<?php

namespace App\Services;

use App\Enums\DoseStatus;
use App\Models\Child;
use App\Models\ChildDose;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * A child's vaccination schedule with every dose's status on a given day.
 *
 * Rules, applied to each vaccine's doses in order (dose 1, 2, 3…). A dose
 * numbered 0 (e.g. the OPV birth dose) stands alone: it has no previous dose
 * and is never the previous dose of dose 1.
 *
 *   completed  the dose has been given.
 *   missed     not given and the child is past max_age_days (last allowed day
 *              is date of birth + max_age_days), or an earlier dose of the same
 *              vaccine was missed, so the series cannot continue.
 *   upcoming   not yet due, or waiting because dose N-1 hasn't been given yet
 *              (dose N always needs dose N-1 first).
 *   due        from the effective due date through effective due + grace_days.
 *   overdue    after that.
 *
 * Effective due date = max(date of birth + age_days,
 *                          dose N-1 given on + min_interval_days)
 */
final class VaccinationSchedule
{
    /** @var Collection<int, ScheduledDose> */
    private Collection $doses;

    public function __construct(private readonly Child $child, private readonly CarbonImmutable $today)
    {
        $this->doses = $this->resolve();
    }

    /**
     * All doses, ordered by due age, then vaccine, then dose number.
     *
     * @return Collection<int, ScheduledDose>
     */
    public function doses(): Collection
    {
        return $this->doses;
    }

    /**
     * Doses grouped by the age they fall due: ["At birth" => [...], "6 weeks" => [...]].
     *
     * @return Collection<string, Collection<int, ScheduledDose>>
     */
    public function byAge(): Collection
    {
        return $this->doses->groupBy(fn (ScheduledDose $d) => $d->schedule()->ageLabel($d->schedule()->age_days));
    }

    /**
     * @return array<string, int> count per DoseStatus value
     */
    public function counts(): array
    {
        $counts = array_fill_keys(array_column(DoseStatus::cases(), 'value'), 0);

        foreach ($this->doses as $dose) {
            $counts[$dose->status->value]++;
        }

        return $counts;
    }

    /**
     * The most pressing dose: the earliest overdue, else due, else upcoming one.
     */
    public function nextAction(): ?ScheduledDose
    {
        foreach ([DoseStatus::Overdue, DoseStatus::Due, DoseStatus::Upcoming] as $status) {
            $match = $this->doses
                ->filter(fn (ScheduledDose $d) => $d->status === $status && $d->effectiveDueDate !== null)
                ->sortBy(fn (ScheduledDose $d) => $d->effectiveDueDate->timestamp)
                ->first();

            if ($match) {
                return $match;
            }
        }

        return null;
    }

    /**
     * @return Collection<int, ScheduledDose>
     */
    private function resolve(): Collection
    {
        $dob = $this->child->date_of_birth;

        return $this->child->doses
            ->groupBy(fn (ChildDose $d) => $d->scheduleDose->vaccine_id)
            ->flatMap(function (Collection $series) use ($dob) {
                $resolved = [];
                $previous = null;

                foreach ($series->sortBy(fn (ChildDose $d) => $d->scheduleDose->dose_number) as $dose) {
                    if ($dose->scheduleDose->dose_number === 0) {
                        $resolved[] = $this->resolveDose($dose, null, $dob);

                        continue;
                    }

                    $previous = $resolved[] = $this->resolveDose($dose, $previous, $dob);
                }

                return $resolved;
            })
            ->sortBy([
                fn (ScheduledDose $a, ScheduledDose $b) => $a->schedule()->age_days <=> $b->schedule()->age_days,
                fn (ScheduledDose $a, ScheduledDose $b) => $a->schedule()->vaccine->name <=> $b->schedule()->vaccine->name,
                fn (ScheduledDose $a, ScheduledDose $b) => $a->schedule()->dose_number <=> $b->schedule()->dose_number,
            ])
            ->values();
    }

    private function resolveDose(ChildDose $dose, ?ScheduledDose $previous, CarbonImmutable $dob): ScheduledDose
    {
        $schedule = $dose->scheduleDose;
        $ageDate = $dob->addDays($schedule->age_days);
        $lastDate = $schedule->max_age_days !== null ? $dob->addDays($schedule->max_age_days) : null;

        $effective = $ageDate;
        if ($previous?->dose->administered_at) {
            $effective = $effective->max($previous->dose->administered_at->addDays($schedule->min_interval_days));
        }

        if ($dose->isAdministered()) {
            return new ScheduledDose($dose, DoseStatus::Completed, $effective, null, $lastDate);
        }

        if ($previous?->status === DoseStatus::Missed) {
            return new ScheduledDose($dose, DoseStatus::Missed, null, null, $lastDate, missedReason: 'series');
        }

        if ($lastDate !== null && $this->today->gt($lastDate)) {
            return new ScheduledDose($dose, DoseStatus::Missed, $effective, null, $lastDate, missedReason: 'age');
        }

        if ($previous !== null && ! $previous->dose->isAdministered()) {
            return new ScheduledDose($dose, DoseStatus::Upcoming, null, null, $lastDate, waitingFor: $previous->dose);
        }

        $overdueFrom = $effective->addDays($schedule->grace_days + 1);

        $status = match (true) {
            $this->today->lt($effective) => DoseStatus::Upcoming,
            $this->today->lt($overdueFrom) => DoseStatus::Due,
            default => DoseStatus::Overdue,
        };

        return new ScheduledDose($dose, $status, $effective, $overdueFrom, $lastDate);
    }
}
