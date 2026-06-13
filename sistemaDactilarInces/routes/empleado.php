<?php

use App\Http\Controllers\EmpleadoController;
use Illuminate\Support\Facades\Route;

Route::get('/index', [EmpleadoController::class, 'index'])->middleware('privilegio:ver empleados');
Route::get('/search/{identificacion}', [EmpleadoController::class, 'search'])->middleware('privilegio:ver empleados');
Route::get('/edit-data/{id?}', [EmpleadoController::class, 'editData'])->middleware('privilegio:ver empleados');
Route::post('/store', [EmpleadoController::class, 'store'])->middleware('privilegio:crear empleado');
Route::put('/update/{id}', [EmpleadoController::class, 'update'])->middleware('privilegio:editar empleado');
Route::delete('/{id}/delete', [EmpleadoController::class, 'delete'])->middleware('privilegio:eliminar empleado');
