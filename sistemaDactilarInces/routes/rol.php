<?php

use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::get('/index', [RoleController::class, 'index'])->middleware('privilegio:ver roles');
Route::post('/store', [RoleController::class, 'store'])->middleware('privilegio:crear rol');
Route::put('/update/{id}', [RoleController::class, 'update'])->middleware('privilegio:editar rol');
Route::delete('/{id}/delete', [RoleController::class, 'delete'])->middleware('privilegio:eliminar rol');
