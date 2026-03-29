<?php

use App\Http\Controllers\Authentication;
use Illuminate\Support\Facades\Route;

// Rutas públicas (sin middleware)
Route::post('/login', [Authentication::class, 'login']);
Route::post('/forgot-password', [Authentication::class, 'forgotPassword']);
Route::post('/reset-password', [Authentication::class, 'resetPassword']);

// Ruta protegida (con middleware)
Route::middleware('jwt.auth')->group(function () {
    Route::post('/logout', [Authentication::class, 'logout']);
});
