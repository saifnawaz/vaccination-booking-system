<?php

use App\Enums\DoseStatus;
use App\Models\Child;
use App\Models\ChildDose;
use App\Models\Vaccine;
use App\Models\VaccineSchedule;
use App\Services\ScheduledDose;
use App\Services\ScheduleGenerator;
use Carbon\CarbonImmutable;
use Database\Seeders\EpiScheduleSeeder;

/**
 * The schedule rules, tested at their boundaries. "Today" is passed explicitly,
 * so these tests never depend on the real date.
 */
beforeEach(function () {
    $this->dob = CarbonImmutable::parse('2026-01-01');
    $this->generator = new ScheduleGenerator;
});

/** A two-dose vaccine: dose 1 at 42 days (max age 105), dose 2 at 70 days. */
function twoDoseVaccine(array $dose1 = [], array $dose2 = []): Vaccine
{
    $vaccine = Vaccine::factory()->create(['code' => 'TWO']);
    VaccineSchedule::factory()->for($vaccine)->create(['dose_number' => 1, 'label' => 'Two-1', 'age_days' => 42, ...$dose1]);
    VaccineSchedule::factory()->for($vaccine)->create(['dose_number' => 2, 'label' => 'Two-2', 'age_days' => 70, ...$dose2]);

    return $vaccine;
}

function childBorn(CarbonImmutable $dob): Child
{
    $child = Child::factory()->create(['date_of_birth' => $dob->toDateString()]);
    (new ScheduleGenerator)->generate($child);

    return $child->fresh();
}

function doseOn(Child $child, string $label, CarbonImmutable $today): ScheduledDose
{
    return $child->fresh()->schedule($today)->doses()->first(fn (ScheduledDose $d) => $d->label() === $label);
}

function give(Child $child, string $label, CarbonImmutable $on): void
{
    $dose = $child->doses()->whereHas('scheduleDose', fn ($q) => $q->where('label', $label))->sole();
    $dose->administered_at = $on;
    $dose->save();
}

test('adding a child generates every scheduled dose from the date of birth', function () {
    $this->seed(EpiScheduleSeeder::class);
    $child = childBorn($this->dob);

    expect($child->doses()->count())->toBe(VaccineSchedule::count());

    $child->doses()->with('scheduleDose')->get()->each(fn (ChildDose $dose) => expect($dose->due_date->toDateString())
        ->toBe($this->dob->addDays($dose->scheduleDose->age_days)->toDateString()));
});

test('generating twice does not duplicate doses', function () {
    $this->seed(EpiScheduleSeeder::class);
    $child = childBorn($this->dob);

    $this->generator->generate($child);

    expect($child->doses()->count())->toBe(VaccineSchedule::count());
});

test('deleted vaccines are left out of new schedules', function () {
    $kept = twoDoseVaccine();
    $removed = Vaccine::factory()->create();
    VaccineSchedule::factory()->for($removed)->create();
    $removed->delete();

    $child = childBorn($this->dob);

    expect($child->doses()->count())->toBe(2);
});

test('a dose is upcoming before, due during, and overdue after its window', function (int $day, DoseStatus $expected) {
    twoDoseVaccine(['grace_days' => 28, 'max_age_days' => null]);
    $child = childBorn($this->dob);

    expect(doseOn($child, 'Two-1', $this->dob->addDays($day))->status)->toBe($expected);
})->with([
    'day before due' => [41, DoseStatus::Upcoming],
    'due date' => [42, DoseStatus::Due],
    'last day of grace' => [70, DoseStatus::Due],
    'first overdue day' => [71, DoseStatus::Overdue],
]);

test('dose N waits for dose N-1 even after its own age date', function () {
    twoDoseVaccine();
    $child = childBorn($this->dob);

    $dose2 = doseOn($child, 'Two-2', $this->dob->addDays(90));

    expect($dose2->status)->toBe(DoseStatus::Upcoming)
        ->and($dose2->waitingFor->scheduleDose->label)->toBe('Two-1')
        ->and($dose2->effectiveDueDate)->toBeNull()
        ->and($dose2->summary())->toBe('After Two-1 has been given');
});

test('the effective due date respects the minimum interval after the previous dose', function () {
    twoDoseVaccine(dose2: ['min_interval_days' => 28]);
    $child = childBorn($this->dob);
    give($child, 'Two-1', $this->dob->addDays(60)); // given late, on day 60

    // Age date for dose 2 is day 70, but day 60 + 28 = day 88 is later.
    $dose2 = doseOn($child, 'Two-2', $this->dob->addDays(80));

    expect($dose2->effectiveDueDate->toDateString())->toBe($this->dob->addDays(88)->toDateString())
        ->and($dose2->status)->toBe(DoseStatus::Upcoming)
        ->and(doseOn($child, 'Two-2', $this->dob->addDays(88))->status)->toBe(DoseStatus::Due);
});

test('the age date wins when the previous dose was given on time', function () {
    twoDoseVaccine();
    $child = childBorn($this->dob);
    give($child, 'Two-1', $this->dob->addDays(42));

    expect(doseOn($child, 'Two-2', $this->dob->addDays(70))->effectiveDueDate->toDateString())
        ->toBe($this->dob->addDays(70)->toDateString());
});

test('a dose is missed the day after its maximum age', function (int $day, DoseStatus $expected) {
    twoDoseVaccine(['max_age_days' => 105]);
    $child = childBorn($this->dob);

    expect(doseOn($child, 'Two-1', $this->dob->addDays($day))->status)->toBe($expected);
})->with([
    'last allowed day' => [105, DoseStatus::Overdue],
    'day after' => [106, DoseStatus::Missed],
]);

test('when a dose is missed, the rest of its series is missed too', function () {
    twoDoseVaccine(['max_age_days' => 105], ['max_age_days' => 240]);
    $child = childBorn($this->dob);

    $dose2 = doseOn($child, 'Two-2', $this->dob->addDays(120));

    expect($dose2->status)->toBe(DoseStatus::Missed)
        ->and($dose2->missedReason)->toBe('series');
});

test('a completed dose stays completed', function () {
    twoDoseVaccine(['max_age_days' => 105]);
    $child = childBorn($this->dob);
    give($child, 'Two-1', $this->dob->addDays(44));

    $dose = doseOn($child, 'Two-1', $this->dob->addDays(400));

    expect($dose->status)->toBe(DoseStatus::Completed)
        ->and($dose->summary())->toStartWith('Given ');
});

test('a dose numbered 0 neither needs nor blocks dose 1', function () {
    $opv = Vaccine::factory()->create(['code' => 'OPVX']);
    VaccineSchedule::factory()->for($opv)->create(['dose_number' => 0, 'label' => 'X-0', 'age_days' => 0, 'max_age_days' => 14]);
    VaccineSchedule::factory()->for($opv)->create(['dose_number' => 1, 'label' => 'X-1', 'age_days' => 42]);
    $child = childBorn($this->dob);

    // X-0 was never given and is now missed, but X-1 is simply due.
    expect(doseOn($child, 'X-0', $this->dob->addDays(45))->status)->toBe(DoseStatus::Missed)
        ->and(doseOn($child, 'X-1', $this->dob->addDays(45))->status)->toBe(DoseStatus::Due);
});

test('next action is the earliest overdue dose before any due one', function () {
    twoDoseVaccine();
    $other = Vaccine::factory()->create();
    VaccineSchedule::factory()->for($other)->create(['label' => 'Late', 'age_days' => 0, 'grace_days' => 7]);
    $child = childBorn($this->dob);

    $schedule = $child->schedule($this->dob->addDays(45)); // Late overdue, Two-1 due

    expect($schedule->nextAction()->label())->toBe('Late')
        ->and($schedule->counts())->toMatchArray(['overdue' => 1, 'due' => 1, 'upcoming' => 1]);
});

test('the real EPI schedule shows every state for a child with gaps', function () {
    $this->seed(EpiScheduleSeeder::class);
    $child = childBorn($this->dob);
    // Birth doses given except OPV-0; nothing since.
    foreach (['BCG', 'Hepatitis B birth dose'] as $label) {
        give($child, $label, $this->dob->addDay());
    }

    $counts = $child->fresh()->schedule($this->dob->addDays(140))->counts();

    expect($counts['completed'])->toBe(2)
        ->and($counts['missed'])->toBe(3)    // OPV-0 (age), Rota-1 (age), Rota-2 (series)
        ->and($counts['overdue'])->toBe(4)   // OPV-1, Penta-1, PCV-1, IPV-1
        ->and($counts['upcoming'])->toBeGreaterThan(0);
});

test('correcting the date of birth moves every due date', function () {
    twoDoseVaccine();
    $child = childBorn($this->dob);

    $child->update(['date_of_birth' => '2026-01-11']);
    $this->generator->recalculateDueDates($child);

    expect($child->doses()->with('scheduleDose')->get()->mapWithKeys(fn ($d) => [$d->scheduleDose->label => $d->due_date->toDateString()])->all())
        ->toBe(['Two-1' => '2026-02-22', 'Two-2' => '2026-03-22']);
});
