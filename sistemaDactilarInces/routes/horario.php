<?php

use App\Http\Controllers\HorarioController;
use Illuminate\Support\Facades\Route;

Route::get('/index', [HorarioController::class, 'index']);
Route::get('/showById/{id}', [HorarioController::class, 'showById']);
Route::post('/store', [HorarioController::class, 'store']);
Route::put('/update/{id}', [HorarioController::class, 'update']);
Route::delete('/delete/{id}', [HorarioController::class, 'delete']);
