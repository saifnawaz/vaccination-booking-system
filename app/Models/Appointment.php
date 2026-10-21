<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Carbon\CarbonImmutable;
use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Nothing is mass-assignable: appointments are created and changed only by
 * App\Services\AppointmentService, which keeps stock and slots consistent.
 */
class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => AppointmentStatus::class,
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
            'no_show_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Child, $this>
     */
    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class)->withTrashed();
    }

    /**
     * @return BelongsTo<ChildDose, $this>
     */
    public function childDose(): BelongsTo
    {
        return $this->belongsTo(ChildDose::class);
    }

    /**
     * @return BelongsTo<Hospital, $this>
     */
    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class)->withTrashed();
    }

    /**
     * @return BelongsTo<TimeSlot, $this>
     */
    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }

    /**
     * The batch holding this appointment's dose.
     *
     * @return BelongsTo<HospitalStock, $this>
     */
    public function hospitalStock(): BelongsTo
    {
        return $this->belongsTo(HospitalStock::class);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->whereIn('status', AppointmentStatus::OPEN);
    }

    public function isOpen(): bool
    {
        return $this->status->isOpen();
    }

    public function startsAt(): CarbonImmutable
    {
        return $this->timeSlot->startsAt();
    }

    /**
     * Parents may cancel or reschedule until this moment.
     */
    public function changeDeadline(): CarbonImmutable
    {
        return $this->startsAt()->subHours(config('vaccination.booking.change_cutoff_hours'));
    }

    public function canBeChangedByParent(?CarbonImmutable $now = null): bool
    {
        return $this->isOpen() && ($now ?? CarbonImmutable::now())->lte($this->changeDeadline());
    }

    public function doseLabel(): string
    {
        return $this->childDose->scheduleDose->label;
    }
}
