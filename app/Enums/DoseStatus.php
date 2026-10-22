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
     * <x-badge> tone: completed green, due and upcoming mustard, overdue red, missed grey.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Completed => 'success',
            self::Due, self::Upcoming => 'warning',
            self::Overdue => 'danger',
            self::Missed => 'neutral',
        };
    }

    /**
     * Shown next to the label, so the status never relies on colour alone.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Completed => 'heroicon-o-check-circle',
            self::Due => 'heroicon-o-bell',
            self::Upcoming => 'heroicon-o-clock',
            self::Overdue => 'heroicon-o-exclamation-circle',
            self::Missed => 'heroicon-o-minus-circle',
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
