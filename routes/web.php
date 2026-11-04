<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\PlaneacionController;
use App\Http\Controllers\Web\SubjectCatalogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - RISA
|--------------------------------------------------------------------------
*/

Route::get('/', [DashboardController::class, 'index'])->name('reticula.index');
Route::get('/reticula', [DashboardController::class, 'index'])->name('reticula.view');

// Planeación: simulador con el motor real (estudiante) y consulta de reglas (administración)
Route::get('/simulador-escenarios', [PlaneacionController::class, 'simulador'])->name('recomendacion.simulador');

// Autenticación y administración del catálogo de materias (solo rol admin, validado en servidor)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('role:admin')->group(function () {
    Route::get('/catalogo-materias', [SubjectCatalogController::class, 'index'])->name('catalogo.materias');
    Route::get('/configuracion-reglas', [PlaneacionController::class, 'reglas'])->name('reglas.configuracion');

    Route::prefix('admin/catalogo')->group(function () {
        Route::get('/materias', [SubjectCatalogController::class, 'list']);
        Route::post('/materias', [SubjectCatalogController::class, 'store']);
        Route::put('/materias/{subject}', [SubjectCatalogController::class, 'update']);
        Route::get('/materias/{subject}/impacto', [SubjectCatalogController::class, 'impacto']);
        Route::delete('/materias/{subject}', [SubjectCatalogController::class, 'destroy']);
        Route::post('/materias/{subject}/reactivar', [SubjectCatalogController::class, 'reactivar']);
        Route::get('/bitacora', [SubjectCatalogController::class, 'bitacora']);
    });
});
