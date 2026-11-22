<?php

namespace Database\Factories;

use App\Enums\HospitalStatus;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Hospital>
 */
class HospitalFactory extends Factory
{
    /**
     * New hospitals start pending, like a real self-registration.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->hospital(),
            'name' => fake()->unique()->lastName().' Children\'s Clinic',
            'address' => fake()->streetAddress(),
            'city' => fake()->randomElement(['Lahore', 'Karachi', 'Islamabad', 'Rawalpindi', 'Faisalabad']),
            'phone' => fake()->numerify('042-#######'),
            'license_number' => fake()->bothify('LIC-####-??'),
            'status' => HospitalStatus::Pending,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => HospitalStatus::Active,
            'approved_at' => now(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['status' => HospitalStatus::Inactive]);
    }
}
