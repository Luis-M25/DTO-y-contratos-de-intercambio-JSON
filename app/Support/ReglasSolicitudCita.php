<?php

namespace App\Support;

final class ReglasSolicitudCita
{
    /**
     * Reglas del contrato común de una solicitud de cita.
     *
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'paciente_id' => ['required', 'uuid', 'exists:pacientes,id'],
            'fecha' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'tipo' => ['required', 'string', 'in:consulta,tratamiento,control'],
            'motivo' => ['required', 'string', 'max:255'],
            'canal' => ['required', 'string', 'in:web,movil'],
            'observacion' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Mensajes en español para el contrato común.
     *
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'paciente_id.required' => 'El paciente es obligatorio.',
            'paciente_id.uuid' => 'El identificador del paciente debe ser un UUID válido.',
            'paciente_id.exists' => 'El paciente indicado no existe.',
            'fecha.required' => 'La fecha de la cita es obligatoria.',
            'fecha.date_format' => 'La fecha debe tener el formato AAAA-MM-DD.',
            'fecha.after_or_equal' => 'La fecha debe ser hoy o posterior.',
            'hora_inicio.required' => 'La hora de inicio es obligatoria.',
            'hora_inicio.date_format' => 'La hora de inicio debe tener el formato HH:MM.',
            'hora_fin.required' => 'La hora de fin es obligatoria.',
            'hora_fin.date_format' => 'La hora de fin debe tener el formato HH:MM.',
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
            'tipo.required' => 'El tipo de cita es obligatorio.',
            'tipo.in' => 'El tipo debe ser consulta, tratamiento o control.',
            'motivo.required' => 'El motivo es obligatorio.',
            'motivo.max' => 'El motivo no debe superar los 255 caracteres.',
            'canal.required' => 'El canal es obligatorio.',
            'canal.in' => 'El canal debe ser web o movil.',
            'observacion.max' => 'La observación no debe superar los 500 caracteres.',
        ];
    }
}
