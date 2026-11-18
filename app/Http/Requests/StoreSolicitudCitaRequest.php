<?php

namespace App\Http\Requests;

use App\Support\ReglasSolicitudCita;
use Illuminate\Foundation\Http\FormRequest;

class StoreSolicitudCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ReglasSolicitudCita::rules();
    }

    public function messages(): array
    {
        return ReglasSolicitudCita::messages();
    }
}
