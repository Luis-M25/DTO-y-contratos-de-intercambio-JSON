<?php

namespace App\Services;

use App\DTO\SolicitudCitaData;
use App\Models\SolicitudCita;
use Illuminate\Support\Collection;

class SolicitudCitaService
{
    public function registrar(SolicitudCitaData $data): SolicitudCita
    {
        return SolicitudCita::create([
            ...$data->toModelAttributes(),
            'estado' => 'pendiente',
        ]);
    }

    public function listar(): Collection
    {
        return SolicitudCita::with('paciente')->latest()->get();
    }

    public function obtenerPorId(string $id): ?SolicitudCita
    {
        return SolicitudCita::with('paciente')->find($id);
    }
}
