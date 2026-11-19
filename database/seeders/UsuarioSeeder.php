<?php

namespace Database\Seeders;

use App\Models\Paciente;
use App\Models\User;
use Illuminate\Database\Seeder;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $pacienteA = Paciente::where('codigo_paciente', 'PAC-0001')->first();
        $pacienteB = Paciente::where('codigo_paciente', 'PAC-0002')->first();

        $this->crearUsuario(
            email: (string) env('DEMO_DOCTORA_EMAIL', 'doctora@demo.test'),
            password: env('DEMO_DOCTORA_PASSWORD'),
            role: User::ROLE_DOCTORA,
            name: 'Doctora Demo',
        );

        $this->crearUsuario(
            email: (string) env('DEMO_ASISTENTE_EMAIL', 'asistente@demo.test'),
            password: env('DEMO_ASISTENTE_PASSWORD'),
            role: User::ROLE_ASISTENTE,
            name: 'Asistente Demo',
        );

        $this->crearUsuario(
            email: (string) env('DEMO_PACIENTE_A_EMAIL', 'paciente.a@demo.test'),
            password: env('DEMO_PACIENTE_A_PASSWORD'),
            role: User::ROLE_PACIENTE,
            name: $pacienteA?->nombres ?? 'Paciente Demo A',
            pacienteId: $pacienteA?->id,
        );

        $this->crearUsuario(
            email: (string) env('DEMO_PACIENTE_B_EMAIL', 'paciente.b@demo.test'),
            password: env('DEMO_PACIENTE_B_PASSWORD'),
            role: User::ROLE_PACIENTE,
            name: $pacienteB?->nombres ?? 'Paciente Demo B',
            pacienteId: $pacienteB?->id,
        );
    }

    private function crearUsuario(
        string $email,
        mixed $password,
        string $role,
        string $name,
        ?string $pacienteId = null,
    ): void {
        if (! is_string($password) || trim($password) === '') {
            $this->command?->warn(
                "No se creó el usuario {$email}: falta definir su contraseña en .env."
            );

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => $password,
                'role' => $role,
                'paciente_id' => $pacienteId,
            ],
        );
    }
}
