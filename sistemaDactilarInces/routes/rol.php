<?php

use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::get('/index', [RoleController::class, 'index']);
Route::get('/showById/{id}', [RoleController::class, 'showById']);
Route::post('/store', [RoleController::class, 'store']);
Route::put('/update/{id}', [RoleController::class, 'update']);
Route::delete('/delete/{id}', [RoleController::class, 'delete']);
