<?php

use App\Http\Controllers\InasistenciaController;
use Illuminate\Support\Facades\Route;

Route::get('/index', [InasistenciaController::class, 'index'])->middleware('privilegio:ver inasistencias');
Route::get('/showById/{id}', [InasistenciaController::class, 'showById'])->middleware('privilegio:ver inasistencias');
Route::post('/store', [InasistenciaController::class, 'store'])->middleware('privilegio:registrar inasistencia');
Route::put('/update/{id}', [InasistenciaController::class, 'update'])->middleware('privilegio:editar inasistencia');
Route::delete('/{id}/delete', [InasistenciaController::class, 'delete'])->middleware('privilegio:eliminar inasistencia');
