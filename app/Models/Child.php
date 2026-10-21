<?php

namespace App\Models;

use App\Enums\Gender;
use App\Services\VaccinationSchedule;
use Carbon\CarbonImmutable;
use Database\Factories\ChildFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

// parent_id is set from the signed-in parent, never from request input.
#[Fillable(['first_name', 'last_name', 'date_of_birth', 'gender', 'blood_group', 'medical_notes'])]
class Child extends Model
{
    /** @use HasFactory<ChildFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'children';

    public const BLOOD_GROUPS = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'immutable_date',
            'gender' => Gender::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    /**
     * @return HasMany<ChildDose, $this>
     */
    public function doses(): HasMany
    {
        return $this->hasMany(ChildDose::class);
    }

    /**
     * @return HasMany<Appointment, $this>
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function fullName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Age in words, e.g. "5 days", "7 weeks", "10 months", "2 years 3 months".
     */
    public function ageLabel(?CarbonImmutable $on = null): string
    {
        $on ??= CarbonImmutable::today();
        $days = (int) $this->date_of_birth->diffInDays($on);
        $months = (int) $this->date_of_birth->diffInMonths($on);

        $plural = fn (int $n, string $unit) => $n.' '.str($unit)->plural($n);

        return match (true) {
            $days < 14 => $plural($days, 'day'),
            $days < 91 => $plural(intdiv($days, 7), 'week'),
            $months < 24 => $plural($months, 'month'),
            default => $plural(intdiv($months, 12), 'year').($months % 12 ? ' '.$plural($months % 12, 'month') : ''),
        };
    }

    /**
     * Every scheduled dose with its status today. Loads doses if needed.
     */
    public function schedule(?CarbonImmutable $today = null): VaccinationSchedule
    {
        $this->loadMissing('doses.scheduleDose.vaccine', 'doses.administeredBy');

        return new VaccinationSchedule($this, $today ?? CarbonImmutable::today());
    }
}
