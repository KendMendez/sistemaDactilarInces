<?php

use App\Http\Controllers\FeriadoController;
use Illuminate\Support\Facades\Route;

Route::get('/index', [FeriadoController::class, 'index'])->middleware('privilegio:ver feriados');
Route::post('/store', [FeriadoController::class, 'store'])->middleware('privilegio:crear feriado');
Route::put('/update/{id}', [FeriadoController::class, 'update'])->middleware('privilegio:editar feriado');
Route::delete('/{id}/delete', [FeriadoController::class, 'delete'])->middleware('privilegio:eliminar feriado');
