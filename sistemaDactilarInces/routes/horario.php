<?php

use App\Http\Controllers\HorarioController;
use Illuminate\Support\Facades\Route;

Route::get('/index', [HorarioController::class, 'index'])->middleware('privilegio:ver horarios');
Route::post('/store', [HorarioController::class, 'store'])->middleware('privilegio:crear horario');
Route::put('/update/{id}', [HorarioController::class, 'update'])->middleware('privilegio:editar horario');
Route::delete('/{id}/delete', [HorarioController::class, 'delete'])->middleware('privilegio:eliminar horario');
