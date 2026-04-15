<?php

use App\Http\Controllers\PrivilegioController;
use Illuminate\Support\Facades\Route;

Route::get('/index', [PrivilegioController::class, 'index']);
Route::get('/showById/{id}', [PrivilegioController::class, 'showById']);
Route::post('/store', [PrivilegioController::class, 'store']);
Route::put('/update/{id}', [PrivilegioController::class, 'update']);
Route::delete('/delete/{id}', [PrivilegioController::class, 'delete']);
