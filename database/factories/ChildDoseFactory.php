<?php

namespace Database\Factories;

use App\Models\Child;
use App\Models\ChildDose;
use App\Models\VaccineSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChildDose>
 */
class ChildDoseFactory extends Factory
{
    /**
     * Prefer App\Services\ScheduleGenerator for real schedules; this is for isolated tests.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'child_id' => Child::factory(),
            'vaccine_schedule_id' => VaccineSchedule::factory(),
            'due_date' => now()->toDateString(),
            'administered_at' => null,
        ];
    }
}
