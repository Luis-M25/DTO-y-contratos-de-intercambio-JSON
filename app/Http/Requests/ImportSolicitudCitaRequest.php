<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportSolicitudCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'origen' => ['required', 'string', 'in:formulario_web,servicio_externo'],
            'datos' => ['required', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'origen.required' => 'El origen es obligatorio.',
            'origen.in' => 'El origen debe ser formulario_web o servicio_externo.',
            'datos.required' => 'Los datos de origen son obligatorios.',
            'datos.array' => 'Los datos de origen deben ser un objeto JSON.',
        ];
    }
}
