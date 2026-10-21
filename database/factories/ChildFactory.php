<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Models\Child;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Child>
 */
class ChildFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parent_id' => User::factory()->parent(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'date_of_birth' => now()->subDays(fake()->numberBetween(0, 700))->toDateString(),
            'gender' => fake()->randomElement(Gender::cases()),
            'blood_group' => null,
            'medical_notes' => null,
        ];
    }

    public function bornDaysAgo(int $days): static
    {
        return $this->state(fn (array $attributes) => ['date_of_birth' => now()->subDays($days)->toDateString()]);
    }
}
