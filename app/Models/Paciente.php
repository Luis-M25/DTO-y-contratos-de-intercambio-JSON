<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paciente extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'codigo_paciente',
        'nombres',
        'cedula',
        'telefono',
        'direccion',
        'fecha_nacimiento',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date:Y-m-d',
        ];
    }

    public function solicitudesCita(): HasMany
    {
        return $this->hasMany(SolicitudCita::class);
    }
}
