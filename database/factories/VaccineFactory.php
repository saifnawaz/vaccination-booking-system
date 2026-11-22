<?php

namespace Database\Factories;

use App\Models\Vaccine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vaccine>
 */
class VaccineFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Test vaccine '.fake()->unique()->numberBetween(1, 99999),
            'code' => 'V'.fake()->unique()->numerify('#####'),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
