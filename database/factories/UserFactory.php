<?php

namespace Database\Factories;

use App\Models\Group;
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
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'role' => User::ROLE_SAAS_ADMIN,
            'group_id' => null,
            'password' => static::$password ??= Hash::make('password'),
            'must_change_password' => false,
            'is_active' => true,
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

    public function saasAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_SAAS_ADMIN,
            'group_id' => null,
        ]);
    }

    public function groupAdmin(?Group $group = null): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_GROUP_ADMIN,
            'group_id' => $group?->id ?? Group::factory(),
            'must_change_password' => true,
        ]);
    }
}
