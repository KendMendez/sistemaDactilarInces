<?php

use App\Http\Controllers\AsistenciaController;
use Illuminate\Support\Facades\Route;

Route::get('/index', [AsistenciaController::class, 'index']);
Route::get('/showById/{id}', [AsistenciaController::class, 'showById']);
Route::post('/store', [AsistenciaController::class, 'store']);
Route::put('/update/{id}', [AsistenciaController::class, 'update']);
Route::delete('/delete/{id}', [AsistenciaController::class, 'delete']);
