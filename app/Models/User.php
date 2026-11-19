<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_DOCTORA = 'doctora';

    public const ROLE_ASISTENTE = 'asistente';

    public const ROLE_PACIENTE = 'paciente';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'paciente_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isDoctora(): bool
    {
        return $this->role === self::ROLE_DOCTORA;
    }

    public function isAsistente(): bool
    {
        return $this->role === self::ROLE_ASISTENTE;
    }

    public function isPaciente(): bool
    {
        return $this->role === self::ROLE_PACIENTE;
    }

    public function esPersonalClinico(): bool
    {
        return $this->hasRole(self::ROLE_DOCTORA, self::ROLE_ASISTENTE);
    }
}
