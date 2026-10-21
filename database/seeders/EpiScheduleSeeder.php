<?php

namespace Database\Seeders;

use App\Models\Vaccine;
use App\Models\VaccineSchedule;
use Illuminate\Database\Seeder;

/**
 * Loads the routine schedule from config/epi.php into vaccines and vaccine_schedule.
 * The schedule MUST be verified against the official EPI Pakistan schedule: see config/epi.php.
 *
 * Safe to re-run after editing the config: rows are matched by vaccine code and
 * dose number and updated in place. Run it on its own with:
 *     php artisan db:seed --class=EpiScheduleSeeder
 */
class EpiScheduleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('epi.vaccines') as $code => $definition) {
            $vaccine = Vaccine::withTrashed()->updateOrCreate(
                ['code' => $code],
                ['name' => $definition['name'], 'description' => $definition['description']],
            );

            foreach ($definition['doses'] as $dose) {
                VaccineSchedule::updateOrCreate(
                    ['vaccine_id' => $vaccine->id, 'dose_number' => $dose['dose']],
                    [
                        'label' => $dose['label'],
                        'age_days' => $dose['age_days'],
                        'min_interval_days' => $dose['min_interval_days'] ?? config('vaccination.default_min_interval_days'),
                        'max_age_days' => $dose['max_age_days'] ?? null,
                        'grace_days' => $dose['grace_days'] ?? config('vaccination.default_grace_days'),
                    ],
                );
            }
        }
    }
}
