<?php

namespace Database\Factories;

use App\Models\Hospital;
use App\Models\TimeSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimeSlot>
 */
class TimeSlotFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hospital_id' => Hospital::factory()->active(),
            'date' => now()->addDays(7)->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'capacity' => 4,
        ];
    }
}
