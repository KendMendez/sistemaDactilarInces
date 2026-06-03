<?php

use App\Http\Controllers\KioskoController;
use Illuminate\Support\Facades\Route;

Route::middleware('kiosko')->group(function () {
    Route::post('/verificar', [KioskoController::class, 'verificar']);
    Route::post('/match', [KioskoController::class, 'match']);
});
