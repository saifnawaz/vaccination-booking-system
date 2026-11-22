<?php

namespace Database\Factories;

use App\Models\Hospital;
use App\Models\HospitalStock;
use App\Models\Vaccine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HospitalStock>
 */
class HospitalStockFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hospital_id' => Hospital::factory()->active(),
            'vaccine_id' => Vaccine::factory(),
            'batch_number' => fake()->unique()->bothify('BATCH-####-??'),
            'expiry_date' => now()->addMonths(6)->toDateString(),
            'quantity' => 50,
            'reserved' => 0,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => ['expiry_date' => now()->subDays(5)->toDateString()]);
    }

    public function expiringSoon(): static
    {
        return $this->state(fn (array $attributes) => ['expiry_date' => now()->addDays(10)->toDateString()]);
    }
}
