<?php

use App\Http\Controllers\EmpleadoController;
use Illuminate\Support\Facades\Route;

Route::get('/index', [EmpleadoController::class, 'index']);
Route::get('/showById/{id}', [EmpleadoController::class, 'showById']);
Route::post('/store', [EmpleadoController::class, 'store']);
Route::put('/update/{id}', [EmpleadoController::class, 'update']);
Route::delete('/delete/{id}', [EmpleadoController::class, 'delete']);
