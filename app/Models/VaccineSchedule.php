<?php

namespace App\Models;

use Database\Factories\VaccineScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One dose of the routine schedule: which vaccine, which dose, due at what age.
 */
#[Fillable(['vaccine_id', 'dose_number', 'label', 'age_days', 'min_interval_days', 'max_age_days', 'grace_days'])]
class VaccineSchedule extends Model
{
    /** @use HasFactory<VaccineScheduleFactory> */
    use HasFactory;

    protected $table = 'vaccine_schedule';

    protected function casts(): array
    {
        return [
            'dose_number' => 'integer',
            'age_days' => 'integer',
            'min_interval_days' => 'integer',
            'max_age_days' => 'integer',
            'grace_days' => 'integer',
        ];
    }

    /**
     * Includes soft-deleted vaccines, so existing schedules still show their name.
     *
     * @return BelongsTo<Vaccine, $this>
     */
    public function vaccine(): BelongsTo
    {
        return $this->belongsTo(Vaccine::class)->withTrashed();
    }

    /**
     * Human label for the age a dose is due, e.g. "At birth", "6 weeks", "9 months".
     */
    public static function ageLabel(int $days): string
    {
        return match (true) {
            $days === 0 => 'At birth',
            $days < 182 && $days % 7 === 0 => ($days / 7).' weeks',
            default => (int) round($days / 30).' months',
        };
    }
}
