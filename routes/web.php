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
