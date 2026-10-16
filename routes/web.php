<?php

use App\Http\Controllers\Web\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - RISA
|--------------------------------------------------------------------------
*/

Route::get('/', [DashboardController::class, 'index'])->name('reticula.index');
Route::get('/reticula', [DashboardController::class, 'index'])->name('reticula.view');

// Módulos complementarios y maquetas de trabajo del equipo
Route::get('/configuracion-reglas', function () {
    return response()->file(base_path('MockupReglasConfiguracion_v1_2026-10-12.html'));
})->name('reglas.configuracion');

Route::get('/simulador-escenarios', function () {
    return response()->file(base_path('MockupRecomendacion_v1_2026-10-14.html'));
})->name('recomendacion.simulador');
