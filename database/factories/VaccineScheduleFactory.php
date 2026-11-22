<?php

namespace Database\Factories;

use App\Models\Vaccine;
use App\Models\VaccineSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VaccineSchedule>
 */
class VaccineScheduleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vaccine_id' => Vaccine::factory(),
            'dose_number' => 1,
            'label' => 'Dose '.fake()->unique()->numberBetween(1, 99999),
            'age_days' => 0,
            'min_interval_days' => 28,
            'max_age_days' => null,
            'grace_days' => 28,
        ];
    }
}
