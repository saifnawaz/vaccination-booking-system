<?php

namespace App\Enums;

/**
 * How a completed dose was recorded.
 */
enum DoseSource: string
{
    /** Given at a hospital through an appointment in this app. */
    case GivenHere = 'given_here';

    /** Copied by a hospital from the child's previous records, e.g. a vaccination card. */
    case HistoricalRecord = 'historical_record';

    public function label(): string
    {
        return match ($this) {
            self::GivenHere => 'Given here',
            self::HistoricalRecord => 'Recorded from previous records',
        };
    }
}
