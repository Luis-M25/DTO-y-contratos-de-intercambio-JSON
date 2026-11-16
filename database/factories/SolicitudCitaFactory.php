<?php

namespace Database\Factories;

use App\Models\Paciente;
use App\Models\SolicitudCita;
use Illuminate\Database\Eloquent\Factories\Factory;

class SolicitudCitaFactory extends Factory
{
    protected $model = SolicitudCita::class;

    public function definition(): array
    {
        return [
            'paciente_id' => Paciente::factory(),
            'fecha' => now()->addDays(3)->format('Y-m-d'),
            'hora_inicio' => '09:00',
            'hora_fin' => '09:30',
            'tipo' => 'consulta',
            'motivo' => 'Consulta de control',
            'canal' => 'web',
            'observacion' => null,
            'estado' => 'pendiente',
        ];
    }
}
