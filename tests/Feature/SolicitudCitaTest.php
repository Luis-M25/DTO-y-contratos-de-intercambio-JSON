<?php

namespace Tests\Feature;

use App\Models\Paciente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SolicitudCitaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-15 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function payload(array $overrides = []): array
    {
        $paciente = Paciente::factory()->create();

        return array_merge([
            'paciente_id' => $paciente->id,
            'fecha' => '2026-11-20',
            'hora_inicio' => '09:30',
            'hora_fin' => '10:00',
            'tipo' => 'consulta',
            'motivo' => 'Dolor dental',
            'canal' => 'web',
            'observacion' => 'Primera consulta',
        ], $overrides);
    }

    public function test_p1_registra_solicitud_valida(): void
    {
        $response = $this->postJson('/api/v1/solicitudes-cita', $this->payload());

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'solicitud_id',
                    'paciente_id',
                    'fecha',
                    'hora_inicio',
                    'hora_fin',
                    'tipo',
                    'canal',
                    'estado',
                    'creado_en',
                ],
                'mensaje',
            ])
            ->assertJsonPath('data.estado', 'pendiente')
            ->assertJsonPath('data.fecha', '2026-11-20')
            ->assertJsonPath('data.hora_inicio', '09:30');

        $this->assertDatabaseHas('solicitud_citas', [
            'paciente_id' => $response->json('data.paciente_id'),
            'fecha' => '2026-11-20',
            'estado' => 'pendiente',
        ]);
    }

    public function test_p2_rechaza_solicitud_sin_campo_obligatorio(): void
    {
        $payload = $this->payload();
        unset($payload['motivo']);

        $response = $this->postJson('/api/v1/solicitudes-cita', $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['motivo'])
            ->assertJsonMissingPath('data');

        $this->assertDatabaseCount('solicitud_citas', 0);
    }

    public function test_p3_rechaza_tipo_de_dato_incorrecto(): void
    {
        $payload = $this->payload(['paciente_id' => ['no-es-uuid']]);

        $response = $this->postJson('/api/v1/solicitudes-cita', $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['paciente_id'])
            ->assertJsonMissingPath('data');

        $this->assertDatabaseCount('solicitud_citas', 0);
    }

    public function test_p4_rechaza_fecha_con_formato_incorrecto(): void
    {
        $response = $this->postJson(
            '/api/v1/solicitudes-cita',
            $this->payload(['fecha' => '20/11/2026'])
        );

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['fecha']);

        $this->assertDatabaseCount('solicitud_citas', 0);
    }

    public function test_p5_rechaza_hora_fin_anterior(): void
    {
        $response = $this->postJson(
            '/api/v1/solicitudes-cita',
            $this->payload(['hora_fin' => '09:00'])
        );

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['hora_fin']);

        $this->assertDatabaseCount('solicitud_citas', 0);
    }
}
