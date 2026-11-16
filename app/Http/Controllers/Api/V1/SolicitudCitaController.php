<?php

namespace App\Http\Controllers\Api\V1;

use App\DTO\SolicitudCitaData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSolicitudCitaRequest;
use App\Http\Resources\SolicitudCitaResource;
use App\Services\SolicitudCitaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SolicitudCitaController extends Controller
{
    public function __construct(
        private readonly SolicitudCitaService $service
    ) {
    }

    public function index(): AnonymousResourceCollection
    {
        return SolicitudCitaResource::collection($this->service->listar());
    }

    public function store(StoreSolicitudCitaRequest $request): JsonResponse
    {
        $solicitud = $this->service->registrar(
            SolicitudCitaData::fromValidated($request->validated())
        );

        return response()->json([
            'data' => new SolicitudCitaResource($solicitud),
            'mensaje' => 'Solicitud de cita registrada correctamente.',
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $solicitud = $this->service->obtenerPorId($id);

        if (! $solicitud) {
            return response()->json([
                'message' => 'Solicitud de cita no encontrada.',
            ], 404);
        }

        return response()->json([
            'data' => new SolicitudCitaResource($solicitud),
        ]);
    }
}
