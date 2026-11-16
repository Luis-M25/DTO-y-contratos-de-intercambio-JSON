<?php

namespace App\DTO;

final readonly class SolicitudCitaData
{
    public function __construct(
        public string $pacienteId,
        public string $fecha,
        public string $horaInicio,
        public string $horaFin,
        public string $tipo,
        public string $motivo,
        public string $canal,
        public ?string $observacion,
    ) {
    }

    public static function fromValidated(array $data): self
    {
        return new self(
            pacienteId: $data['paciente_id'],
            fecha: $data['fecha'],
            horaInicio: $data['hora_inicio'],
            horaFin: $data['hora_fin'],
            tipo: $data['tipo'],
            motivo: $data['motivo'],
            canal: $data['canal'],
            observacion: $data['observacion'] ?? null,
        );
    }

    public function toModelAttributes(): array
    {
        return [
            'paciente_id' => $this->pacienteId,
            'fecha' => $this->fecha,
            'hora_inicio' => $this->horaInicio,
            'hora_fin' => $this->horaFin,
            'tipo' => $this->tipo,
            'motivo' => $this->motivo,
            'canal' => $this->canal,
            'observacion' => $this->observacion,
        ];
    }
}
