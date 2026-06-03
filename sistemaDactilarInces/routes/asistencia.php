<?php

use App\Http\Controllers\AsistenciaController;
use Illuminate\Support\Facades\Route;

Route::get('/index', [AsistenciaController::class, 'index'])->middleware('privilegio:ver asistencias');
Route::post('/store', [AsistenciaController::class, 'store'])->middleware('privilegio:registrar asistencia');
Route::put('/update/{id}', [AsistenciaController::class, 'update'])->middleware('privilegio:editar asistencia');
Route::delete('/{id}/delete', [AsistenciaController::class, 'delete'])->middleware('privilegio:eliminar asistencia');
Route::get('/pending', [AsistenciaController::class, 'pending'])->middleware('privilegio:ver asistencias');
Route::put('/{id}/approve', [AsistenciaController::class, 'approve'])->middleware('privilegio:aprobar asistencia');
Route::put('/{id}/reject', [AsistenciaController::class, 'reject'])->middleware('privilegio:aprobar asistencia');
