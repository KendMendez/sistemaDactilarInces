<?php

use App\Http\Controllers\KioskoController;
use Illuminate\Support\Facades\Route;

Route::middleware('kiosko')->group(function () {
    Route::get('/templates', [KioskoController::class, 'templates']);
    Route::post('/verificar', [KioskoController::class, 'verificar']);
});
