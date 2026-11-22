<?php

namespace App\Services;

use App\Models\Hospital;
use App\Models\TimeSlot;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Builds a hospital's time slots in bulk.
 */
final class SlotGenerator
{
    /**
     * Every slot of $minutes between $startTime and $endTime on the chosen weekdays
     * (ISO: 1 = Monday … 7 = Sunday) from $from to $to. Slots overlapping an
     * existing slot, or already in the past, are skipped rather than duplicated.
     *
     * @param  list<int>  $weekdays
     * @return array{created: int, skipped: int}
     *
     * @throws InvalidArgumentException when more than the configured maximum would be created
     */
    public function generate(
        Hospital $hospital,
        CarbonImmutable $from,
        CarbonImmutable $to,
        array $weekdays,
        string $startTime,
        string $endTime,
        int $minutes,
        int $capacity,
    ): array {
        $candidates = $this->candidates($from, $to, $weekdays, $startTime, $endTime, $minutes);

        $max = config('vaccination.slots.bulk_max_slots');
        if (count($candidates) > $max) {
            throw new InvalidArgumentException('That would create '.count($candidates)." slots. The most you can create at once is {$max}.");
        }

        $existing = $hospital->timeSlots()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->get(['date', 'start_time', 'end_time'])
            ->groupBy(fn (TimeSlot $slot) => $slot->date->toDateString());

        $now = CarbonImmutable::now();
        $rows = [];
        $skipped = 0;

        foreach ($candidates as [$date, $start, $end]) {
            $clashes = ($existing[$date] ?? collect())
                ->contains(fn (TimeSlot $slot) => $slot->start_time < $end && $slot->end_time > $start);

            if ($clashes || CarbonImmutable::parse("{$date} {$start}")->lte($now)) {
                $skipped++;

                continue;
            }

            $rows[] = [
                'hospital_id' => $hospital->id,
                'date' => $date,
                'start_time' => $start,
                'end_time' => $end,
                'capacity' => $capacity,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::transaction(function () use ($rows) {
            foreach (array_chunk($rows, 200) as $chunk) {
                TimeSlot::insert($chunk);
            }
        });

        return ['created' => count($rows), 'skipped' => $skipped];
    }

    /**
     * @param  list<int>  $weekdays
     * @return list<array{0: string, 1: string, 2: string}> [date, start H:i:s, end H:i:s]
     */
    private function candidates(
        CarbonImmutable $from,
        CarbonImmutable $to,
        array $weekdays,
        string $startTime,
        string $endTime,
        int $minutes,
    ): array {
        $slots = [];

        foreach (CarbonPeriod::create($from, $to) as $day) {
            if (! in_array($day->isoWeekday(), $weekdays, false)) {
                continue;
            }

            $date = $day->toDateString();
            $cursor = CarbonImmutable::parse("{$date} {$startTime}");
            $close = CarbonImmutable::parse("{$date} {$endTime}");

            while ($cursor->addMinutes($minutes)->lte($close)) {
                $slots[] = [$date, $cursor->format('H:i:s'), $cursor->addMinutes($minutes)->format('H:i:s')];
                $cursor = $cursor->addMinutes($minutes);
            }
        }

        return $slots;
    }
}
