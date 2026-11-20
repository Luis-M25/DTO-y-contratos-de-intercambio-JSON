<?php

namespace Tests\Feature;

use App\Models\Paciente;
use App\Models\SolicitudCita;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NormalizacionSolicitudCitaTest extends TestCase
{
    use RefreshDatabase;

    private Paciente $paciente;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-17 10:00:00');

        Sanctum::actingAs(User::factory()->doctora()->create());

        $this->paciente = Paciente::factory()->create([
            'codigo_paciente' => 'PAC-9001',
            'nombres' => 'Ana Torres',
            'cedula' => '0102030405',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * @return array<string, mixed>
     */
    private function datosFormulario(array $overrides = []): array
    {
        return array_merge([
            'cedula' => '0102030405',
            'fechaCita' => '20/11/2026',
            'horaInicio' => '3:30 PM',
            'horaFin' => '4:00 PM',
            'tipoCita' => 'Consulta',
            'motivo' => 'Dolor dental',
            'observacion' => '',
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    private function datosServicio(array $overrides = []): array
    {
        $datos = [
            'patient' => [
                'document_number' => '010-203-0405',
            ],
            'appointment' => [
                'date' => '2026-11-20',
                'start_time' => '15:30',
                'end_time' => '16:00',
                'type' => 'consultation',
            ],
            'reason' => 'Dolor dental',
            'notes' => null,
        ];

        return array_replace_recursive($datos, $overrides);
    }

    private function importar(string $origen, array $datos)
    {
        return $this->postJson('/api/v1/solicitudes-cita/importar', [
            'origen' => $origen,
            'datos' => $datos,
        ]);
    }

    public function test_p1_normaliza_formulario_web_valido_y_registra_la_cita(): void
    {
        $response = $this->importar('formulario_web', $this->datosFormulario());

        $response->assertCreated()
            ->assertJsonPath('data.paciente_id', $this->paciente->id)
            ->assertJsonPath('data.fecha', '2026-11-20')
            ->assertJsonPath('data.hora_inicio', '15:30')
            ->assertJsonPath('data.hora_fin', '16:00')
            ->assertJsonPath('data.tipo', 'consulta')
            ->assertJsonPath('data.canal', 'web')
            ->assertJsonPath('data.estado', 'pendiente');

        $this->assertDatabaseHas('solicitud_citas', [
            'paciente_id' => $this->paciente->id,
            'fecha' => '2026-11-20',
            'tipo' => 'consulta',
            'motivo' => 'Dolor dental',
            'canal' => 'web',
        ]);

        $solicitud = SolicitudCita::query()->firstOrFail();

        $this->assertSame('15:30', Carbon::parse($solicitud->hora_inicio)->format('H:i'));
        $this->assertSame('16:00', Carbon::parse($solicitud->hora_fin)->format('H:i'));
        $this->assertNull($solicitud->observacion);
    }

    public function test_p2_normaliza_servicio_externo_equivalente_y_genera_el_mismo_contrato(): void
    {
        $p1 = $this->importar('formulario_web', $this->datosFormulario())
            ->assertCreated()
            ->json('data');

        $p2 = $this->importar('servicio_externo', $this->datosServicio())
            ->assertCreated()
            ->json('data');

        foreach (['paciente_id', 'fecha', 'hora_inicio', 'hora_fin', 'tipo', 'estado'] as $campo) {
            $this->assertSame($p1[$campo], $p2[$campo], "El campo {$campo} no coincide entre las dos entradas.");
        }

        $this->assertSame('web', $p1['canal']);
        $this->assertSame('movil', $p2['canal']);
        $this->assertNotSame($p1['solicitud_id'], $p2['solicitud_id']);
        $this->assertDatabaseCount('solicitud_citas', 2);
    }

    public function test_p3_rechaza_formulario_sin_motivo_y_no_registra_nada(): void
    {
        $datos = $this->datosFormulario();
        unset($datos['motivo']);

        $response = $this->importar('formulario_web', $datos);

        $response->assertUnprocessable()
            ->assertJsonPath('origen', 'formulario_web')
            ->assertJsonStructure(['message', 'errors' => ['motivo']]);

        $this->assertDatabaseCount('solicitud_citas', 0);
    }

    public function test_p4_rechaza_fecha_inexistente_del_formulario(): void
    {
        $response = $this->importar(
            'formulario_web',
            $this->datosFormulario(['fechaCita' => '31/02/2026']),
        );

        $response->assertUnprocessable()
            ->assertJsonPath('errors.fecha.0', 'La fecha indicada no existe en el calendario.');

        $this->assertDatabaseCount('solicitud_citas', 0);
    }

    public function test_p5_rechaza_hora_fin_anterior_en_el_formulario(): void
    {
        $response = $this->importar(
            'formulario_web',
            $this->datosFormulario(['horaFin' => '2:00 PM']),
        );

        $response->assertUnprocessable()
            ->assertJsonPath('errors.hora_fin.0', 'La hora de fin debe ser posterior a la hora de inicio.');

        $this->assertDatabaseCount('solicitud_citas', 0);
    }

    public function test_p6_rechaza_tipo_no_pertinente_en_lugar_de_convertirlo_silenciosamente(): void
    {
        $response = $this->importar(
            'formulario_web',
            $this->datosFormulario(['tipoCita' => 'Consulta general']),
        );

        $response->assertUnprocessable()
            ->assertJsonPath('errors.tipo.0', 'El tipo de cita debe ser Consulta, Tratamiento o Control.');

        $this->assertDatabaseCount('solicitud_citas', 0);
    }

    public function test_p7_rechaza_cedula_inexistente_en_el_servicio_externo(): void
    {
        $response = $this->importar(
            'servicio_externo',
            $this->datosServicio([
                'patient' => ['document_number' => '999-999-9999'],
            ]),
        );

        $response->assertUnprocessable()
            ->assertJsonPath('errors.paciente_id.0', 'No existe un paciente registrado con la cédula 9999999999.')
            ->assertJsonPath('origen', 'servicio_externo');

        $this->assertDatabaseCount('solicitud_citas', 0);
    }

    public function test_p8_convierte_observacion_vacia_en_null(): void
    {
        $response = $this->importar(
            'formulario_web',
            $this->datosFormulario(['observacion' => '   ']),
        );

        $response->assertCreated();

        $this->assertNull(SolicitudCita::query()->firstOrFail()->observacion);
    }
}
