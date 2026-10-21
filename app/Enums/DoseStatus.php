<?php

namespace App\Enums;

/**
 * Where a child's scheduled dose stands today. Never stored: worked out by
 * App\Services\VaccinationSchedule from the dates.
 */
enum DoseStatus: string
{
    case Completed = 'completed';
    case Due = 'due';
    case Upcoming = 'upcoming';
    case Overdue = 'overdue';
    case Missed = 'missed';

    public function label(): string
    {
        return match ($this) {
            self::Completed => 'Completed',
            self::Due => 'Due now',
            self::Upcoming => 'Upcoming',
            self::Overdue => 'Overdue',
            self::Missed => 'Missed',
        };
    }

    /**
     * <x-badge> tone: green completed, amber due, red overdue.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Completed => 'success',
            self::Due => 'warning',
            self::Overdue => 'danger',
            self::Upcoming => 'primary',
            self::Missed => 'neutral',
        };
    }

    /**
     * Doses a parent should act on now.
     */
    public function needsAction(): bool
    {
        return $this === self::Due || $this === self::Overdue;
    }
}
