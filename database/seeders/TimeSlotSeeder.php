<?php

namespace Database\Seeders;

use App\Enums\HospitalStatus;
use App\Models\Hospital;
use App\Services\SlotGenerator;
use Illuminate\Database\Seeder;

/**
 * Three weeks of Mon–Sat morning slots at each active hospital.
 */
class TimeSlotSeeder extends Seeder
{
    public function run(SlotGenerator $generator): void
    {
        Hospital::where('status', HospitalStatus::Active)->get()->each(function (Hospital $hospital, int $i) use ($generator) {
            $generator->generate(
                $hospital,
                today()->toImmutable(),
                today()->addDays(21)->toImmutable(),
                [1, 2, 3, 4, 5, 6],
                $i === 0 ? '09:00' : '10:00',
                $i === 0 ? '13:00' : '14:00',
                30,
                4,
            );
        });
    }
}
