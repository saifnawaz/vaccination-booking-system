<?php

namespace App\Services;

use App\Enums\DoseStatus;
use App\Models\ChildDose;
use App\Models\VaccineSchedule;
use Carbon\CarbonImmutable;

/**
 * One dose of a child's schedule with its status worked out for a given day.
 */
final readonly class ScheduledDose
{
    public function __construct(
        public ChildDose $dose,
        public DoseStatus $status,
        /** Earliest day it may be given; null while waiting for the previous dose. */
        public ?CarbonImmutable $effectiveDueDate,
        /** First overdue day; null when not applicable. */
        public ?CarbonImmutable $overdueFrom,
        /** Last day it may be given (from max_age_days); null when there is no age limit. */
        public ?CarbonImmutable $lastDate,
        /** The earlier dose of the same vaccine that must be given first, if still outstanding. */
        public ?ChildDose $waitingFor = null,
        /** Why a dose is missed: "age" (too old) or "series" (an earlier dose was missed). */
        public ?string $missedReason = null,
    ) {}

    public function schedule(): VaccineSchedule
    {
        return $this->dose->scheduleDose;
    }

    /**
     * Can be booked for some date: due, overdue (catch-up) or upcoming with a known
     * due date. Not when completed, missed, or still waiting for the previous dose.
     */
    public function isBookable(): bool
    {
        return in_array($this->status, [DoseStatus::Due, DoseStatus::Overdue, DoseStatus::Upcoming], true)
            && $this->effectiveDueDate !== null;
    }

    /**
     * Whether the dose may be given on $date: not before it is due, not after its age limit.
     */
    public function canBeGivenOn(CarbonImmutable $date): bool
    {
        return $this->isBookable()
            && $date->gte($this->effectiveDueDate)
            && ($this->lastDate === null || $date->lte($this->lastDate));
    }

    public function label(): string
    {
        return $this->schedule()->label;
    }

    /**
     * One-line explanation for the parent, e.g. "Overdue since 3 Mar 2026".
     */
    public function summary(): string
    {
        $date = fn (CarbonImmutable $d) => $d->format('j M Y');

        return match ($this->status) {
            DoseStatus::Completed => $this->dose->isHistoricalRecord()
                ? 'Given '.$date($this->dose->administered_at).' · recorded from previous records'
                    .($this->dose->administeredBy ? ' by '.$this->dose->administeredBy->name : '')
                : 'Given '.$date($this->dose->administered_at)
                    .($this->dose->administeredBy ? ' at '.$this->dose->administeredBy->name : ''),
            DoseStatus::Missed => $this->missedReason === 'series'
                ? 'Can no longer be given because an earlier dose was missed'
                : 'Could be given until '.$date($this->lastDate),
            DoseStatus::Overdue => 'Overdue since '.$date($this->overdueFrom),
            DoseStatus::Due => 'Due since '.$date($this->effectiveDueDate)
                .($this->lastDate ? ', give by '.$date($this->lastDate) : ''),
            DoseStatus::Upcoming => $this->waitingFor
                ? 'After '.$this->waitingFor->scheduleDose->label.' has been given'
                : 'Due from '.$date($this->effectiveDueDate),
        };
    }
}
