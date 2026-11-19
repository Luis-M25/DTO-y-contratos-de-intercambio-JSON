<?php

namespace App\Policies;

use App\Models\SolicitudCita;
use App\Models\User;

class SolicitudCitaPolicy
{
    public function viewAny(User $user): bool
    {
        // Cualquier usuario autenticado puede listar; el controlador filtra
        // las solicitudes de un paciente para que solo vea las propias.
        return true;
    }

    public function view(User $user, SolicitudCita $solicitud): bool
    {
        return $this->esPersonalClinico($user)
            || $user->paciente_id === $solicitud->paciente_id;
    }

    public function create(User $user, string $pacienteId): bool
    {
        return $this->esPersonalClinico($user)
            || $user->paciente_id === $pacienteId;
    }

    public function delete(User $user, SolicitudCita $solicitud): bool
    {
        return $user->isDoctora();
    }

    private function esPersonalClinico(User $user): bool
    {
        return $user->hasRole(User::ROLE_DOCTORA, User::ROLE_ASISTENTE);
    }
}
