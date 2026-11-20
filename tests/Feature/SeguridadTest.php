<?php

namespace Tests\Feature;

use App\Models\Paciente;
use App\Models\SolicitudCita;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SeguridadTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function payload(Paciente $paciente, array $overrides = []): array
    {
        return array_merge([
            'paciente_id' => $paciente->id,
            'fecha' => now()->addDays(2)->format('Y-m-d'),
            'hora_inicio' => '09:00',
            'hora_fin' => '09:30',
            'tipo' => 'consulta',
            'motivo' => 'Control de seguridad',
            'canal' => 'web',
            'observacion' => null,
        ], $overrides);
    }

    public function test_p1_acceso_permitido_con_autenticacion_y_permisos(): void
    {
        $doctora = User::factory()->doctora()->create();
        $paciente = Paciente::factory()->create();
        $solicitud = SolicitudCita::factory()->for($paciente)->create();

        Sanctum::actingAs($doctora);

        $this->getJson('/api/v1/solicitudes-cita')
            ->assertOk()
            ->assertJsonPath('data.0.solicitud_id', $solicitud->id);

        $this->postJson('/api/v1/solicitudes-cita', $this->payload($paciente))
            ->assertCreated()
            ->assertJsonPath('data.paciente_id', $paciente->id);
    }

    public function test_p2_sin_autenticacion_devuelve_401(): void
    {
        $this->getJson('/api/v1/solicitudes-cita')->assertUnauthorized();
        $this->getJson('/api/v1/pacientes')->assertUnauthorized();

        $this->withHeader('Authorization', 'Bearer is803_token-invalido')
            ->getJson('/api/v1/solicitudes-cita')
            ->assertUnauthorized();
    }

    public function test_p3_usuario_autenticado_sin_permisos_devuelve_403(): void
    {
        $pacienteA = Paciente::factory()->create();
        $pacienteB = Paciente::factory()->create();
        $usuarioA = User::factory()->paciente($pacienteA)->create();
        $solicitudB = SolicitudCita::factory()->for($pacienteB)->create();

        Sanctum::actingAs($usuarioA);

        // No puede consultar una solicitud ajena.
        $this->getJson("/api/v1/solicitudes-cita/{$solicitudB->id}")
            ->assertForbidden();

        // No puede crear una solicitud para otro paciente.
        $this->postJson('/api/v1/solicitudes-cita', $this->payload($pacienteB))
            ->assertForbidden();

        // No puede listar el catálogo general de pacientes.
        $this->getJson('/api/v1/pacientes')->assertForbidden();

        // La asistente no puede eliminar solicitudes.
        Sanctum::actingAs(User::factory()->asistente()->create());
        $this->deleteJson("/api/v1/solicitudes-cita/{$solicitudB->id}")
            ->assertForbidden();
    }

    public function test_p4_configuracion_sin_secretos_expuestos(): void
    {
        $gitignore = (string) file_get_contents(base_path('.gitignore'));
        $this->assertMatchesRegularExpression('/^\.env$/m', $gitignore);

        $envExample = (string) file_get_contents(base_path('.env.example'));

        foreach (['DOCTORA', 'ASISTENTE', 'PACIENTE_A', 'PACIENTE_B'] as $rol) {
            $this->assertMatchesRegularExpression(
                '/^DEMO_'.$rol.'_PASSWORD=\s*$/m',
                $envExample
            );
        }

        $password = Str::password(20);

        $user = User::factory()->doctora()->create([
            'email' => 'p4@demo.test',
            'password' => $password,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.role', User::ROLE_DOCTORA)
            ->assertJsonMissingPath('data.user.password');

        $this->assertStringNotContainsString($password, $response->getContent());
    }

    public function test_p5_logout_invalida_el_token(): void
    {
        $user = User::factory()->doctora()->create();
        $token = $user->createToken('seguridad-test')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        // Simula un cliente nuevo: evita que el guard conservado por la
        // aplicación de pruebas autentique la siguiente petición.
        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/solicitudes-cita')
            ->assertUnauthorized();
    }

    public function test_p6_paciente_solo_lista_sus_propias_solicitudes(): void
    {
        $pacienteA = Paciente::factory()->create();
        $pacienteB = Paciente::factory()->create();
        $usuarioA = User::factory()->paciente($pacienteA)->create();

        $solicitudA = SolicitudCita::factory()->for($pacienteA)->create();
        SolicitudCita::factory()->for($pacienteB)->create();

        Sanctum::actingAs($usuarioA);

        $this->getJson('/api/v1/solicitudes-cita')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.solicitud_id', $solicitudA->id)
            ->assertJsonPath('data.0.paciente_id', $pacienteA->id);
    }

    public function test_p7_doctora_puede_eliminar_una_solicitud(): void
    {
        $doctora = User::factory()->doctora()->create();
        $solicitud = SolicitudCita::factory()->create();

        Sanctum::actingAs($doctora);

        $this->deleteJson("/api/v1/solicitudes-cita/{$solicitud->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('solicitud_citas', ['id' => $solicitud->id]);
    }

    public function test_p8_login_invalido_no_revela_credenciales(): void
    {
        $password = Str::password(20);
        $wrongPassword = Str::password(20);

        $user = User::factory()->doctora()->create([
            'email' => 'p8@demo.test',
            'password' => $password,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => $wrongPassword,
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Las credenciales no son correctas.')
            ->assertJsonValidationErrors('email');

        $this->assertStringNotContainsString($wrongPassword, $response->getContent());
        $this->assertStringNotContainsString($password, $response->getContent());
    }

    public function test_p9_rate_limit_bloquea_intentos_repetidos_de_login(): void
    {
        $credenciales = [
            'email' => 'rate-limit@demo.test',
            'password' => Str::password(20),
        ];

        for ($intento = 1; $intento <= 5; $intento++) {
            $this->postJson('/api/v1/auth/login', $credenciales)
                ->assertUnprocessable();
        }

        $this->postJson('/api/v1/auth/login', $credenciales)
            ->assertStatus(429);
    }
}
