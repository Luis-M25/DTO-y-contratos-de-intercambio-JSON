<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class SolicitudCitaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'solicitud_id' => $this->id,
            'paciente_id' => $this->paciente_id,
            'fecha' => $this->fecha->format('Y-m-d'),
            'hora_inicio' => Carbon::parse($this->hora_inicio)->format('H:i'),
            'hora_fin' => Carbon::parse($this->hora_fin)->format('H:i'),
            'tipo' => $this->tipo,
            'canal' => $this->canal,
            'estado' => $this->estado,
            'creado_en' => $this->created_at->toIso8601String(),
        ];
    }
}
