<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * The users table has never had a `name` column — the factory wrote to
     * one anyway, so every factory-made user blew up on insert.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name'         => fake()->name(),
            'employee_code'     => strtoupper(fake()->unique()->bothify('EMP-####')),
            'email'             => fake()->unique()->safeEmail(),
            'phone'             => fake()->numerify('03#########'),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'user_role'         => 'user',
            'is_active'         => true,
            'joining_date'      => fake()->dateTimeBetween('-3 years')->format('Y-m-d'),
            'remember_token'    => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => ['user_role' => 'admin']);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => ['email_verified_at' => null]);
    }
}
