<?php

use App\Http\Controllers\Authentication;
use Illuminate\Support\Facades\Route;

Route::post('/login', [Authentication::class, 'login']);

Route::middleware('jwt.auth')->group(function () {
    Route::post('/logout', [Authentication::class, 'logout']);
});
