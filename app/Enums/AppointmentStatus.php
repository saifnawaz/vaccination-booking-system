<?php

namespace App\Enums;

/**
 * pending → approved → completed
 * pending | approved → rejected (hospital) or cancelled (parent)
 * approved → no_show (hospital, after the slot has started)
 * Rescheduling keeps an open appointment open and sets it back to pending.
 */
enum AppointmentStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case Completed = 'completed';
    case NoShow = 'no_show';

    /** Statuses that hold a slot place and a reserved dose. */
    public const OPEN = ['pending', 'approved'];

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Awaiting confirmation',
            self::Approved => 'Confirmed',
            self::Rejected => 'Declined',
            self::Cancelled => 'Cancelled',
            self::Completed => 'Completed',
            self::NoShow => 'Missed appointment',
        };
    }

    /**
     * Red is reserved for overdue doses, errors and destructive actions, so a
     * declined or missed appointment is grey or mustard, not red.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Pending, self::NoShow => 'warning',
            self::Approved => 'primary',
            self::Completed => 'success',
            self::Rejected, self::Cancelled => 'neutral',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'heroicon-o-clock',
            self::Approved => 'heroicon-o-calendar-days',
            self::Completed => 'heroicon-o-check-circle',
            self::Rejected => 'heroicon-o-x-circle',
            self::Cancelled => 'heroicon-o-minus-circle',
            self::NoShow => 'heroicon-o-exclamation-triangle',
        };
    }

    public function isOpen(): bool
    {
        return in_array($this->value, self::OPEN, true);
    }

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Approved, self::Rejected, self::Cancelled],
            self::Approved => [self::Completed, self::Rejected, self::Cancelled, self::NoShow],
            default => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }
}
