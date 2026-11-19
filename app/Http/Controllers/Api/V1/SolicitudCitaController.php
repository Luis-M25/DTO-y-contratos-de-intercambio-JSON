<?php

namespace App\Http\Controllers\Api\V1;

use App\DTO\SolicitudCitaData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSolicitudCitaRequest;
use App\Http\Resources\SolicitudCitaResource;
use App\Models\SolicitudCita;
use App\Services\SolicitudCitaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class SolicitudCitaController extends Controller
{
    public function __construct(
        private readonly SolicitudCitaService $service
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', SolicitudCita::class);

        return SolicitudCitaResource::collection(
            $this->service->listarPara($request->user())
        );
    }

    public function store(StoreSolicitudCitaRequest $request): JsonResponse
    {
        $datos = $request->validated();

        $this->authorize('create', [SolicitudCita::class, $datos['paciente_id']]);

        $solicitud = $this->service->registrar(
            SolicitudCitaData::fromValidated($datos)
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

        $this->authorize('view', $solicitud);

        return response()->json([
            'data' => new SolicitudCitaResource($solicitud),
        ]);
    }

    public function destroy(string $id): Response
    {
        $solicitud = $this->service->obtenerPorId($id);

        if (! $solicitud) {
            return response()->json([
                'message' => 'Solicitud de cita no encontrada.',
            ], 404);
        }

        $this->authorize('delete', $solicitud);
        $this->service->eliminar($solicitud);

        return response()->noContent();
    }
}
