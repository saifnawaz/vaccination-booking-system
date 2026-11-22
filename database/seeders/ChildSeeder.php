<?php

namespace Database\Seeders;

use App\Enums\Gender;
use App\Enums\HospitalStatus;
use App\Enums\Role;
use App\Models\Child;
use App\Models\ChildDose;
use App\Models\Hospital;
use App\Models\HospitalStock;
use App\Models\User;
use App\Services\ScheduleGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * 20 children of varied ages with realistic vaccination histories, so that every
 * schedule state appears: completed, due, upcoming, overdue and missed.
 * Names are invented. Ages are relative to the day the seeder runs.
 *
 * Each child: [parent number, first name, last name, gender, age in days,
 *              doses given up to this age (days), labels NOT given even if in range, days late each dose was given]
 */
class ChildSeeder extends Seeder
{
    private const CHILDREN = [
        // Newborns: birth doses due.
        [1, 'Ali', 'Khan', 'male', 3, null, [], 0],
        [7, 'Ayaan', 'Qureshi', 'male', 2, null, [], 0],
        [10, 'Rayan', 'Farooq', 'male', 10, 0, ['Hepatitis B birth dose'], 1],     // Hep B still due (limit 14 days)

        // Upcoming: birth doses done, 6-week doses not yet due.
        [8, 'Hira', 'Sheikh', 'female', 28, 0, [], 1],
        [2, 'Hassan', 'Ahmed', 'male', 35, 0, [], 1],

        // Due now.
        [2, 'Noor', 'Ahmed', 'female', 49, 0, [], 1],                            // 6-week doses due
        [3, 'Amna', 'Malik', 'female', 77, 42, [], 3],                           // 10-week doses due
        [8, 'Saad', 'Sheikh', 'male', 101, 70, [], 2],                           // 14-week doses due
        [5, 'Ahmed', 'Siddiqui', 'male', 290, 98, [], 2],                        // 9-month doses due
        [6, 'Omar', 'Raza', 'male', 465, 270, [], 3],                            // MR-2 due

        // Overdue.
        [3, 'Ibrahim', 'Malik', 'male', 84, 0, [], 1],                           // 6-week doses overdue
        [9, 'Laiba', 'Iqbal', 'female', 56, 0, ['BCG'], 1],                      // BCG overdue, 6-week doses due
        [5, 'Khadija', 'Siddiqui', 'female', 335, 98, [], 2],                    // 9-month doses overdue
        [6, 'Fatima', 'Raza', 'female', 548, 270, [], 2],                        // MR-2 overdue
        [9, 'Musa', 'Iqbal', 'male', 425, 270, ['TCV'], 2],                      // TCV overdue, MR-2 upcoming

        // Missed.
        [4, 'Yusuf', 'Tariq', 'male', 150, 70, ['OPV-0', 'Hepatitis B birth dose'], 2], // birth doses missed, 14-week overdue
        [4, 'Zara', 'Tariq', 'female', 183, 98, ['Rota-1', 'Rota-2'], 2],        // Rota-1 missed, so Rota-2 missed too
        [10, 'Esha', 'Farooq', 'female', 140, 0, [], 1],                         // nothing since birth: Rota missed, rest overdue

        // Fully vaccinated.
        [1, 'Maryam', 'Khan', 'female', 760, 450, [], 2],
        [7, 'Bilal', 'Qureshi', 'male', 1100, 450, [], 3],
    ];

    public function run(ScheduleGenerator $generator): void
    {
        $parents = User::where('role', Role::Parent)->orderBy('id')->get()->values();
        $batches = $this->batchesByVaccine();

        foreach (self::CHILDREN as [$parentNumber, $first, $last, $gender, $ageDays, $givenUpTo, $skip, $daysLate]) {
            $child = new Child([
                'first_name' => $first,
                'last_name' => $last,
                'date_of_birth' => today()->subDays($ageDays)->toDateString(),
                'gender' => Gender::from($gender),
            ]);
            $child->parent_id = $parents[$parentNumber - 1]->id;
            $child->save();

            $generator->generate($child);

            if ($givenUpTo === null) {
                continue;
            }

            $child->doses()->with('scheduleDose')->get()
                ->filter(fn (ChildDose $dose) => $dose->scheduleDose->age_days <= $givenUpTo)
                ->reject(fn (ChildDose $dose) => in_array($dose->scheduleDose->label, $skip, true))
                ->each(function (ChildDose $dose) use ($daysLate, $batches) {
                    $dose->administered_at = $dose->due_date->addDays($daysLate);
                    $dose->hospital_stock_id = $batches->get($dose->scheduleDose->vaccine_id)?->id;
                    $dose->save();
                });
        }
    }

    /**
     * The unexpired batch of each vaccine at the first active hospital (or the second
     * when the first has none), used as the "given from" batch for past doses.
     *
     * @return Collection<int, HospitalStock|null>
     */
    private function batchesByVaccine(): Collection
    {
        $hospitals = Hospital::where('status', HospitalStatus::Active)->orderBy('id')->pluck('id');

        return HospitalStock::whereIn('hospital_id', $hospitals)
            ->whereDate('expiry_date', '>=', today())
            ->orderByRaw('FIELD(hospital_id, '.$hospitals->implode(',').')')
            ->orderByDesc('expiry_date')
            ->get()
            ->groupBy('vaccine_id')
            ->map(fn (Collection $group) => $group->first());
    }
}
