<?php

use App\Http\Controllers\CargoController;
use Illuminate\Support\Facades\Route;

Route::get('/index', [CargoController::class, 'index'])->middleware('privilegio:ver cargos');
Route::get('/showById/{id}', [CargoController::class, 'showById'])->middleware('privilegio:ver cargos');
Route::post('/store', [CargoController::class, 'store'])->middleware('privilegio:crear cargo');
Route::put('/update/{id}', [CargoController::class, 'update'])->middleware('privilegio:editar cargo');
Route::delete('/{id}/delete', [CargoController::class, 'delete'])->middleware('privilegio:eliminar cargo');
