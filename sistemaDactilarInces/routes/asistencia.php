<?php

use App\Http\Controllers\AsistenciaController;
use Illuminate\Support\Facades\Route;

Route::get('/index', [AsistenciaController::class, 'index'])->middleware('privilegio:ver asistencias');
Route::get('/showById/{id}', [AsistenciaController::class, 'showById'])->middleware('privilegio:ver asistencias');
Route::post('/store', [AsistenciaController::class, 'store'])->middleware('privilegio:registrar asistencia');
Route::put('/update/{id}', [AsistenciaController::class, 'update'])->middleware('privilegio:editar asistencia');
Route::delete('/{id}/delete', [AsistenciaController::class, 'delete'])->middleware('privilegio:eliminar asistencia');
