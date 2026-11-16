<?php

namespace Database\Factories;

use App\Models\Paciente;
use Illuminate\Database\Eloquent\Factories\Factory;

class PacienteFactory extends Factory
{
    protected $model = Paciente::class;

    public function definition(): array
    {
        return [
            'codigo_paciente' => 'PAC-'.fake()->unique()->numberBetween(1000, 9999),
            'nombres' => fake()->name(),
            'cedula' => fake()->unique()->numerify('##########'),
            'telefono' => fake()->numerify('09########'),
            'direccion' => fake()->address(),
            'fecha_nacimiento' => fake()->date('Y-m-d', '-18 years'),
        ];
    }
}
