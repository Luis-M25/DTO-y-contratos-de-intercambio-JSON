<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportSolicitudCitaRequest;
use App\Http\Resources\SolicitudCitaResource;
use App\Services\NormalizadorSolicitudCita;
use App\Services\SolicitudCitaService;
use Illuminate\Http\JsonResponse;

class SolicitudCitaImportController extends Controller
{
    public function __construct(
        private readonly NormalizadorSolicitudCita $normalizador,
        private readonly SolicitudCitaService $service,
    ) {
    }

    public function store(ImportSolicitudCitaRequest $request): JsonResponse
    {
        $data = $this->normalizador->normalizar(
            $request->string('origen')->toString(),
            $request->array('datos'),
        );

        $solicitud = $this->service->registrar($data);

        return response()->json([
            'data' => new SolicitudCitaResource($solicitud),
            'mensaje' => 'Solicitud de cita importada y normalizada correctamente.',
        ], 201);
    }
}
