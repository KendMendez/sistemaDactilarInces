<?php

use App\Http\Controllers\FeriadoController;
use Illuminate\Support\Facades\Route;

Route::get('/index', [FeriadoController::class, 'index']);
Route::get('/showById{id}', [FeriadoController::class, 'showById']);
Route::post('/store', [FeriadoController::class, 'store']);
Route::put('/update{id}', [FeriadoController::class, 'update']);
Route::delete('/delete{id}', [FeriadoController::class, 'delete']);
