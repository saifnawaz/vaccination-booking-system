<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            // safeEmail() only uses reserved example.* domains, never real addresses.
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('0300-#######'),
            'role' => Role::Parent,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => ['role' => Role::Admin]);
    }

    /**
     * A hospital login account. Use HospitalFactory for the hospital profile itself.
     */
    public function hospital(): static
    {
        return $this->state(fn (array $attributes) => ['role' => Role::Hospital]);
    }

    public function parent(): static
    {
        return $this->state(fn (array $attributes) => ['role' => Role::Parent]);
    }
}
