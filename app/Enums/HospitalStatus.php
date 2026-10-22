<?php

namespace App\Enums;

enum HospitalStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Awaiting approval',
            self::Active => 'Active',
            self::Inactive => 'Inactive',
        };
    }

    /**
     * <x-badge> tone used wherever this status is shown.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Active => 'success',
            self::Inactive => 'neutral',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'heroicon-o-clock',
            self::Active => 'heroicon-o-check-circle',
            self::Inactive => 'heroicon-o-no-symbol',
        };
    }

    /**
     * Statuses an admin may move a hospital to from this one.
     *
     * pending → active (approve) or inactive (decline)
     * active → inactive (deactivate)
     * inactive → active (reactivate)
     *
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Active, self::Inactive],
            self::Active => [self::Inactive],
            self::Inactive => [self::Active],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }

    /**
     * Label for the admin action that moves a hospital from this status to $next.
     */
    public function actionLabel(self $next): string
    {
        return match ([$this, $next]) {
            [self::Pending, self::Active] => 'Approve',
            [self::Pending, self::Inactive] => 'Decline',
            [self::Active, self::Inactive] => 'Deactivate',
            [self::Inactive, self::Active] => 'Reactivate',
            default => $next->label(),
        };
    }

    /**
     * Past tense of actionLabel(), for confirmation messages ("… has been approved").
     */
    public function actionDoneLabel(self $next): string
    {
        return match ([$this, $next]) {
            [self::Pending, self::Active] => 'approved',
            [self::Pending, self::Inactive] => 'declined',
            [self::Active, self::Inactive] => 'deactivated',
            [self::Inactive, self::Active] => 'reactivated',
            default => 'updated',
        };
    }
}
