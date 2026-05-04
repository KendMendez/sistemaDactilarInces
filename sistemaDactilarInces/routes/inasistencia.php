<?php

use App\Http\Controllers\InasistenciaController;
use Illuminate\Support\Facades\Route;

Route::get('/index', [InasistenciaController::class, 'index']);
Route::get('/showById/{id}', [InasistenciaController::class, 'showById']);
Route::post('/store', [InasistenciaController::class, 'store']);
Route::put('/update/{id}', [InasistenciaController::class, 'update']);
Route::delete('/delete/{id}', [InasistenciaController::class, 'delete']);
