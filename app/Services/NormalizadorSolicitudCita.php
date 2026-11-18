<?php

namespace App\Services;

use App\DTO\SolicitudCitaData;
use App\Exceptions\NormalizacionException;
use App\Models\Paciente;
use App\Support\ReglasSolicitudCita;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class NormalizadorSolicitudCita
{
    public const ORIGEN_FORMULARIO_WEB = 'formulario_web';

    public const ORIGEN_SERVICIO_EXTERNO = 'servicio_externo';

    /**
     * Catálogo del formulario web: valores visibles para el usuario.
     *
     * @var array<string, string>
     */
    private const TIPOS_FORMULARIO = [
        'consulta' => 'consulta',
        'tratamiento' => 'tratamiento',
        'control' => 'control',
    ];

    /**
     * Catálogo del servicio externo: valores en inglés.
     *
     * @var array<string, string>
     */
    private const TIPOS_SERVICIO = [
        'consultation' => 'consulta',
        'treatment' => 'tratamiento',
        'follow_up' => 'control',
        'followup' => 'control',
    ];

    public function normalizar(string $origen, array $datos): SolicitudCitaData
    {
        return match ($origen) {
            self::ORIGEN_FORMULARIO_WEB => $this->desdeFormularioWeb($datos),
            self::ORIGEN_SERVICIO_EXTERNO => $this->desdeServicioExterno($datos),
            default => throw new NormalizacionException(
                "El origen '{$origen}' no está soportado.",
                ['origen' => ['Los valores permitidos son formulario_web y servicio_externo.']],
                $origen,
            ),
        };
    }

    /**
     * Traduce el JSON plano del formulario web al contrato común.
     */
    public function desdeFormularioWeb(array $datos): SolicitudCitaData
    {
        $errores = [];

        $cedula = $this->normalizarCedula($datos['cedula'] ?? null, 'paciente_id', $errores);
        $fecha = $this->normalizarFechaFormulario($datos['fechaCita'] ?? null, $errores);
        $horaInicio = $this->normalizarHora12(
            $datos['horaInicio'] ?? null,
            'hora_inicio',
            'La hora de inicio',
            $errores,
        );
        $horaFin = $this->normalizarHora12(
            $datos['horaFin'] ?? null,
            'hora_fin',
            'La hora de fin',
            $errores,
        );
        $tipo = $this->normalizarTipo(
            $datos['tipoCita'] ?? null,
            self::TIPOS_FORMULARIO,
            'tipo',
            'Consulta, Tratamiento o Control',
            $errores,
        );
        $motivo = $this->normalizarTextoObligatorio($datos['motivo'] ?? null, 'motivo', 'El motivo', $errores);
        $observacion = $this->normalizarTextoOpcional($datos['observacion'] ?? null, 'observacion', $errores);

        $paciente = $this->buscarPaciente($cedula, $errores);

        if ($errores !== []) {
            throw new NormalizacionException(
                'La entrada del formulario web contiene datos inválidos.',
                $errores,
                self::ORIGEN_FORMULARIO_WEB,
            );
        }

        return $this->construirContrato(
            paciente: $paciente,
            fecha: $fecha,
            horaInicio: $horaInicio,
            horaFin: $horaFin,
            tipo: $tipo,
            motivo: $motivo,
            canal: 'web',
            observacion: $observacion,
            origen: self::ORIGEN_FORMULARIO_WEB,
        );
    }

    /**
     * Traduce el JSON anidado del servicio externo al contrato común.
     */
    public function desdeServicioExterno(array $datos): SolicitudCitaData
    {
        $errores = [];

        $cedula = $this->normalizarCedula(
            $this->valorAnidado($datos, 'patient', 'document_number'),
            'paciente_id',
            $errores,
        );
        $fecha = $this->normalizarFechaISO(
            $this->valorAnidado($datos, 'appointment', 'date'),
            $errores,
        );
        $horaInicio = $this->normalizarHora24(
            $this->valorAnidado($datos, 'appointment', 'start_time'),
            'hora_inicio',
            'La hora de inicio',
            $errores,
        );
        $horaFin = $this->normalizarHora24(
            $this->valorAnidado($datos, 'appointment', 'end_time'),
            'hora_fin',
            'La hora de fin',
            $errores,
        );
        $tipo = $this->normalizarTipo(
            $this->valorAnidado($datos, 'appointment', 'type'),
            self::TIPOS_SERVICIO,
            'tipo',
            'consultation, treatment o follow_up',
            $errores,
        );
        $motivo = $this->normalizarTextoObligatorio($datos['reason'] ?? null, 'motivo', 'El motivo', $errores);
        $observacion = $this->normalizarTextoOpcional($datos['notes'] ?? null, 'observacion', $errores);

        $paciente = $this->buscarPaciente($cedula, $errores);

        if ($errores !== []) {
            throw new NormalizacionException(
                'La entrada del servicio externo contiene datos inválidos.',
                $errores,
                self::ORIGEN_SERVICIO_EXTERNO,
            );
        }

        return $this->construirContrato(
            paciente: $paciente,
            fecha: $fecha,
            horaInicio: $horaInicio,
            horaFin: $horaFin,
            tipo: $tipo,
            motivo: $motivo,
            canal: 'movil',
            observacion: $observacion,
            origen: self::ORIGEN_SERVICIO_EXTERNO,
        );
    }

    /**
     * Aplica el contrato común con las mismas reglas que usa el endpoint principal.
     */
    private function construirContrato(
        ?Paciente $paciente,
        ?string $fecha,
        ?string $horaInicio,
        ?string $horaFin,
        ?string $tipo,
        ?string $motivo,
        string $canal,
        ?string $observacion,
        string $origen,
    ): SolicitudCitaData {
        $normalizado = [
            'paciente_id' => $paciente?->id,
            'fecha' => $fecha,
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'tipo' => $tipo,
            'motivo' => $motivo,
            'canal' => $canal,
            'observacion' => $observacion,
        ];

        $validator = Validator::make(
            $normalizado,
            ReglasSolicitudCita::rules(),
            ReglasSolicitudCita::messages(),
        );

        if ($validator->fails()) {
            throw new NormalizacionException(
                'La entrada normalizada no cumple el contrato común.',
                $validator->errors()->toArray(),
                $origen,
            );
        }

        return SolicitudCitaData::fromValidated($validator->validated());
    }

    private function valorAnidado(array $datos, string $grupo, string $campo): mixed
    {
        if (! is_array($datos[$grupo] ?? null)) {
            return null;
        }

        return $datos[$grupo][$campo] ?? null;
    }

    /**
     * @param  array<string, array<int, string>>  $errores
     */
    private function normalizarCedula(mixed $valor, string $campo, array &$errores): ?string
    {
        if (! is_string($valor) && ! is_int($valor)) {
            $errores[$campo][] = 'La cédula del paciente es obligatoria.';

            return null;
        }

        $cedula = preg_replace('/\D+/', '', (string) $valor);

        if ($cedula === null || $cedula === '') {
            $errores[$campo][] = 'La cédula del paciente es obligatoria.';

            return null;
        }

        if (strlen($cedula) !== 10) {
            $errores[$campo][] = 'La cédula debe contener exactamente 10 dígitos.';

            return null;
        }

        return $cedula;
    }

    /**
     * @param  array<string, array<int, string>>  $errores
     */
    private function buscarPaciente(?string $cedula, array &$errores): ?Paciente
    {
        if ($cedula === null) {
            return null;
        }

        $paciente = Paciente::query()->where('cedula', $cedula)->first();

        if ($paciente === null) {
            $errores['paciente_id'][] = "No existe un paciente registrado con la cédula {$cedula}.";
        }

        return $paciente;
    }

    /**
     * @param  array<string, array<int, string>>  $errores
     */
    private function normalizarFechaFormulario(mixed $valor, array &$errores): ?string
    {
        if (! is_string($valor) || trim($valor) === '') {
            $errores['fecha'][] = 'La fecha de la cita es obligatoria.';

            return null;
        }

        $valor = trim($valor);

        if (! preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $valor, $partes)) {
            $errores['fecha'][] = 'La fecha debe tener el formato DD/MM/AAAA.';

            return null;
        }

        if (! checkdate((int) $partes[2], (int) $partes[1], (int) $partes[3])) {
            $errores['fecha'][] = 'La fecha indicada no existe en el calendario.';

            return null;
        }

        return sprintf('%04d-%02d-%02d', (int) $partes[3], (int) $partes[2], (int) $partes[1]);
    }

    /**
     * @param  array<string, array<int, string>>  $errores
     */
    private function normalizarFechaISO(mixed $valor, array &$errores): ?string
    {
        if (! is_string($valor) || trim($valor) === '') {
            $errores['fecha'][] = 'La fecha de la cita es obligatoria.';

            return null;
        }

        $valor = trim($valor);

        if (! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $valor, $partes)) {
            $errores['fecha'][] = 'La fecha debe tener el formato AAAA-MM-DD.';

            return null;
        }

        if (! checkdate((int) $partes[2], (int) $partes[3], (int) $partes[1])) {
            $errores['fecha'][] = 'La fecha indicada no existe en el calendario.';

            return null;
        }

        return $valor;
    }

    /**
     * @param  array<string, array<int, string>>  $errores
     */
    private function normalizarHora12(mixed $valor, string $campo, string $etiqueta, array &$errores): ?string
    {
        if (! is_string($valor) || trim($valor) === '') {
            $errores[$campo][] = "{$etiqueta} es obligatoria.";

            return null;
        }

        $valor = strtoupper((string) preg_replace('/\s+/', '', trim($valor)));

        if (! preg_match('/^(0?[1-9]|1[0-2]):([0-5]\d)(AM|PM)$/', $valor, $partes)) {
            $errores[$campo][] = "{$etiqueta} debe tener el formato H:MM AM/PM.";

            return null;
        }

        $hora = ((int) $partes[1]) % 12;

        if ($partes[3] === 'PM') {
            $hora += 12;
        }

        return sprintf('%02d:%s', $hora, $partes[2]);
    }

    /**
     * @param  array<string, array<int, string>>  $errores
     */
    private function normalizarHora24(mixed $valor, string $campo, string $etiqueta, array &$errores): ?string
    {
        if (! is_string($valor) || trim($valor) === '') {
            $errores[$campo][] = "{$etiqueta} es obligatoria.";

            return null;
        }

        $valor = trim($valor);

        if (! preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $valor, $partes)) {
            $errores[$campo][] = "{$etiqueta} debe tener el formato HH:MM en 24 horas.";

            return null;
        }

        return "{$partes[1]}:{$partes[2]}";
    }

    /**
     * @param  array<string, string>  $mapa
     * @param  array<string, array<int, string>>  $errores
     */
    private function normalizarTipo(
        mixed $valor,
        array $mapa,
        string $campo,
        string $permitidos,
        array &$errores,
    ): ?string {
        if (! is_string($valor) || trim($valor) === '') {
            $errores[$campo][] = 'El tipo de cita es obligatorio.';

            return null;
        }

        $clave = Str::of($valor)->trim()->lower()->ascii()->toString();
        $clave = str_replace(['-', ' '], '_', $clave);

        if (! array_key_exists($clave, $mapa)) {
            $errores[$campo][] = "El tipo de cita debe ser {$permitidos}.";

            return null;
        }

        return $mapa[$clave];
    }

    /**
     * @param  array<string, array<int, string>>  $errores
     */
    private function normalizarTextoObligatorio(
        mixed $valor,
        string $campo,
        string $etiqueta,
        array &$errores,
    ): ?string {
        if (! is_string($valor) || trim($valor) === '') {
            $errores[$campo][] = "{$etiqueta} es obligatorio.";

            return null;
        }

        $texto = trim((string) preg_replace('/\s+/u', ' ', $valor));

        if (mb_strlen($texto) > 255) {
            $errores[$campo][] = "{$etiqueta} no debe superar los 255 caracteres.";

            return null;
        }

        return $texto;
    }

    /**
     * @param  array<string, array<int, string>>  $errores
     */
    private function normalizarTextoOpcional(mixed $valor, string $campo, array &$errores): ?string
    {
        if ($valor === null) {
            return null;
        }

        if (! is_string($valor)) {
            $errores[$campo][] = 'La observación debe ser texto o nulo.';

            return null;
        }

        $texto = trim((string) preg_replace('/\s+/u', ' ', $valor));

        if ($texto === '') {
            return null;
        }

        if (mb_strlen($texto) > 500) {
            $errores[$campo][] = 'La observación no debe superar los 500 caracteres.';

            return null;
        }

        return $texto;
    }
}
