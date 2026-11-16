<?php

namespace Database\Seeders;

use App\Models\Paciente;
use Illuminate\Database\Seeder;

class PacienteSeeder extends Seeder
{
    public function run(): void
    {
        $pacientes = [
            [
                'id' => '11111111-1111-4111-8111-111111111111',
                'codigo_paciente' => 'PAC-0001',
                'nombres' => 'Ana Torres',
                'cedula' => '0102030405',
                'telefono' => '0991112233',
                'direccion' => 'Av. Principal 123',
                'fecha_nacimiento' => '1990-05-12',
            ],
            [
                'id' => '22222222-2222-4222-8222-222222222222',
                'codigo_paciente' => 'PAC-0002',
                'nombres' => 'Luis Pérez',
                'cedula' => '0102030406',
                'telefono' => '0992223344',
                'direccion' => 'Calle Secundaria 456',
                'fecha_nacimiento' => '1985-09-23',
            ],
            [
                'id' => '33333333-3333-4333-8333-333333333333',
                'codigo_paciente' => 'PAC-0003',
                'nombres' => 'María Gómez',
                'cedula' => '0102030407',
                'telefono' => '0993334455',
                'direccion' => 'Av. Central 789',
                'fecha_nacimiento' => '1998-01-30',
            ],
        ];

        foreach ($pacientes as $paciente) {
            if (! Paciente::where('codigo_paciente', $paciente['codigo_paciente'])->exists()) {
                Paciente::forceCreate($paciente);
            }
        }
    }
}
