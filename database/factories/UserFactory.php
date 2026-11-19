<?php

namespace Database\Factories;

use App\Models\Paciente;
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
            'password' => static::$password ??= Hash::make('password'),
            'role' => User::ROLE_PACIENTE,
            'paciente_id' => null,
            'remember_token' => Str::random(10),
        ];
    }

    public function doctora(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_DOCTORA,
            'paciente_id' => null,
        ]);
    }

    public function asistente(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_ASISTENTE,
            'paciente_id' => null,
        ]);
    }

    public function paciente(?Paciente $paciente = null): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_PACIENTE,
            'paciente_id' => $paciente?->id ?? Paciente::factory(),
        ]);
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
}
