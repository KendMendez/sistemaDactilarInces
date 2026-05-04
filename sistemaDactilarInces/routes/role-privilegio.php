<?php

use App\Http\Controllers\RolePrivilegioController;
use Illuminate\Support\Facades\Route;

Route::get('/showByRoleId/{roleId}', [RolePrivilegioController::class, 'findPrivilegiosByRoleId']);
Route::post('/store', [RolePrivilegioController::class, 'store']);
