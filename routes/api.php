<?php

use App\Http\Controllers\Api\V1\PacienteController;
use App\Http\Controllers\Api\V1\SolicitudCitaController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/pacientes', [PacienteController::class, 'index']);

    Route::get('/solicitudes-cita', [SolicitudCitaController::class, 'index']);
    Route::post('/solicitudes-cita', [SolicitudCitaController::class, 'store']);
    Route::get('/solicitudes-cita/{id}', [SolicitudCitaController::class, 'show']);
});
