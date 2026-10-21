<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Carbon\CarbonImmutable;
use Database\Factories\TimeSlotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// hospital_id is set from the signed-in hospital, never from request input.
#[Fillable(['date', 'start_time', 'end_time', 'capacity'])]
class TimeSlot extends Model
{
    /** @use HasFactory<TimeSlotFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'immutable_date',
            'capacity' => 'integer',
            'booked_children' => 'integer', // from scopeWithBookedChildren()
        ];
    }

    /**
     * @return BelongsTo<Hospital, $this>
     */
    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    /**
     * @return HasMany<Appointment, $this>
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Adds `booked_children`: distinct children with an open appointment in the slot.
     * Capacity counts children, so one child having several vaccines takes one place.
     *
     * @param  Builder<self>  $query
     */
    public function scopeWithBookedChildren(Builder $query): void
    {
        $query->addSelect(['booked_children' => Appointment::query()
            ->selectRaw('count(distinct child_id)')
            ->whereColumn('time_slot_id', 'time_slots.id')
            ->whereIn('status', AppointmentStatus::OPEN),
        ]);
    }

    /**
     * Slots with at least one free place.
     *
     * @param  Builder<self>  $query
     */
    public function scopeWithFreePlaces(Builder $query): void
    {
        $query->whereRaw('time_slots.capacity > ('.Appointment::query()
            ->selectRaw('count(distinct child_id)')
            ->whereColumn('time_slot_id', 'time_slots.id')
            ->whereIn('status', AppointmentStatus::OPEN)
            ->toRawSql().')');
    }

    /**
     * Slots that haven't started yet.
     *
     * @param  Builder<self>  $query
     */
    public function scopeNotStarted(Builder $query): void
    {
        $this->scopeStartsAfter($query, CarbonImmutable::now());
    }

    /**
     * Slots starting after $moment (e.g. now + the minimum booking notice).
     *
     * @param  Builder<self>  $query
     */
    public function scopeStartsAfter(Builder $query, CarbonImmutable $moment): void
    {
        $query->where(fn (Builder $q) => $q
            ->whereDate('date', '>', $moment->toDateString())
            ->orWhere(fn (Builder $q) => $q->whereDate('date', $moment->toDateString())->where('start_time', '>', $moment->format('H:i:s'))));
    }

    /**
     * Distinct children holding a place, read fresh from the database.
     */
    public function bookedChildren(): int
    {
        return $this->appointments()->open()->distinct()->count('child_id');
    }

    public function hasStarted(?CarbonImmutable $now = null): bool
    {
        return $this->startsAt()->lte($now ?? CarbonImmutable::now());
    }

    /**
     * "9:00 am – 9:30 am"
     */
    public function timeRange(): string
    {
        return self::formatTime($this->start_time).' – '.self::formatTime($this->end_time);
    }

    public static function formatTime(string $time): string
    {
        return CarbonImmutable::createFromFormat('H:i:s', strlen($time) === 5 ? "{$time}:00" : $time)->format('g:i a');
    }

    public function startsAt(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->date->toDateString().' '.$this->start_time);
    }

    /**
     * Slots at the same hospital and date whose times overlap [start, end).
     *
     * @param  Builder<self>  $query
     */
    public function scopeOverlapping(Builder $query, int $hospitalId, string $date, string $start, string $end): void
    {
        $query->where('hospital_id', $hospitalId)
            ->whereDate('date', $date)
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start);
    }
}
