<?php

use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\ReticulaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas API de RISA (Retícula Interactiva de Seguimiento Académico)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Consulta interactiva de la retícula
    Route::get('/reticula', [ReticulaController::class, 'index']);
    Route::get('/materias/{id}', [ReticulaController::class, 'show']);
    Route::post('/materias/{id}/comentarios', [ReticulaController::class, 'storeComment']);
    Route::post('/historial/actualizar', [ReticulaController::class, 'updateStatus']);

    // Motor de recomendación de carga y proyección
    Route::get('/recomendacion', [RecommendationController::class, 'recommend']);
    Route::post('/recomendacion/guardar', [RecommendationController::class, 'save']);
});

// Alias compatible para consultas directas /api/reticula
Route::get('/reticula', [ReticulaController::class, 'index']);
Route::get('/recomendacion', [RecommendationController::class, 'recommend']);
Route::post('/materias/{id}/comentarios', [ReticulaController::class, 'storeComment']);
Route::post('/historial/actualizar', [ReticulaController::class, 'updateStatus']);
