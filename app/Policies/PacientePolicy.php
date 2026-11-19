<?php

namespace App\Policies;

use App\Models\Paciente;
use App\Models\User;

class PacientePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(User::ROLE_DOCTORA, User::ROLE_ASISTENTE);
    }

    public function view(User $user, Paciente $paciente): bool
    {
        return $user->hasRole(User::ROLE_DOCTORA, User::ROLE_ASISTENTE)
            || $user->paciente_id === $paciente->id;
    }
}
