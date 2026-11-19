<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PacienteController;
use App\Http\Controllers\Api\V1\SolicitudCitaController;
use App\Http\Controllers\Api\V1\SolicitudCitaImportController;
use Illuminate\Support\Facades\Route;

// Rutas públicas (autenticación)
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:auth-login');

    Route::post('/register', [AuthController::class, 'login']); // Alias para compatibilidad con guías anteriores
});

// Rutas protegidas por Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);

    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Usuarios (doctora y asistente)
    Route::middleware('role:doctora,asistente')->prefix('usuarios')->group(function () {
        Route::get('/', [PacienteController::class, 'index']);
    });

    // Pacientes: acceso propio
    Route::middleware('role:paciente')->prefix('pacientes')->group(function () {
        Route::get('/mi-perfil', [PacienteController::class, 'index']); // placeholder
    });

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