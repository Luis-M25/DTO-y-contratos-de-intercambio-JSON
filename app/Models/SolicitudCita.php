<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudCita extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'solicitud_citas';

    protected $fillable = [
        'paciente_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'tipo',
        'motivo',
        'canal',
        'observacion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date:Y-m-d',
        ];
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }
}
