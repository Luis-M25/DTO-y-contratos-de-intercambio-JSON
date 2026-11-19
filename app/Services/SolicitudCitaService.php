<?php

namespace App\Services;

use App\DTO\SolicitudCitaData;
use App\Models\SolicitudCita;
use App\Models\User;
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

    public function listarPara(User $user): Collection
    {
        $consulta = SolicitudCita::with('paciente')->latest();

        if ($user->isPaciente()) {
            $consulta->where('paciente_id', $user->paciente_id);
        }

        return $consulta->get();
    }

    public function obtenerPorId(string $id): ?SolicitudCita
    {
        return SolicitudCita::with('paciente')->find($id);
    }

    public function eliminar(SolicitudCita $solicitud): void
    {
        $solicitud->delete();
    }
}
