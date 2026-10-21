<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\TimeSlotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
