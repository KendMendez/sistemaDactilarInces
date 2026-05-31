<?php

use App\Http\Controllers\RolePrivilegioController;
use Illuminate\Support\Facades\Route;

Route::get('/showByRoleId/{roleId}', [RolePrivilegioController::class, 'findPrivilegiosByRoleId'])->middleware('privilegio:ver roles');
Route::post('/store', [RolePrivilegioController::class, 'store'])->middleware('privilegio:asignar privilegios');
