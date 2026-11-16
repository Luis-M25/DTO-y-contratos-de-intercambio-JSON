<?php

namespace Database\Seeders;

use App\Models\SolicitudCita;
use Illuminate\Database\Seeder;

class SolicitudCitaSeeder extends Seeder
{
    public function run(): void
    {
        if (SolicitudCita::count() === 0) {
            SolicitudCita::create([
                'paciente_id' => '11111111-1111-4111-8111-111111111111',
                'fecha' => now()->addDays(5)->format('Y-m-d'),
                'hora_inicio' => '10:00',
                'hora_fin' => '10:30',
                'tipo' => 'consulta',
                'motivo' => 'Control odontológico',
                'canal' => 'web',
                'observacion' => 'Registro de ejemplo',
                'estado' => 'pendiente',
            ]);
        }
    }
}
