<?php

use App\Http\Controllers\CargoController;
use Illuminate\Support\Facades\Route;

Route::get('/index', [CargoController::class, 'index']);
Route::get('/showById/{id}', [CargoController::class, 'showById']);
Route::post('/store', [CargoController::class, 'store']);
Route::put('/update/{id}', [CargoController::class, 'update']);
Route::delete('/delete/{id}', [CargoController::class, 'delete']);
