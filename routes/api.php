<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PacienteController;
use App\Http\Controllers\Api\V1\SolicitudCitaController;
use App\Http\Controllers\Api\V1\SolicitudCitaImportController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Rutas públicas (autenticación)
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:auth-login');
    });

    // Rutas protegidas por Sanctum
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Pacientes (doctora y asistente)
        Route::get('/pacientes', [PacienteController::class, 'index'])
            ->middleware('role:doctora,asistente');

        // Solicitudes de cita
        Route::prefix('solicitudes-cita')->group(function () {
            // Endpoint de importación normalizada (TT.2.3)
            Route::post('/importar', [SolicitudCitaImportController::class, 'store'])
                ->middleware('role:doctora,asistente');

            // Listado
            Route::get('/', [SolicitudCitaController::class, 'index']);

            // Creación
            Route::post('/', [SolicitudCitaController::class, 'store']);

            // Consulta individual
            Route::get('/{id}', [SolicitudCitaController::class, 'show']);

            // Eliminación (solo doctora)
            Route::delete('/{id}', [SolicitudCitaController::class, 'destroy'])
                ->middleware('role:doctora');
        });
    });
});
