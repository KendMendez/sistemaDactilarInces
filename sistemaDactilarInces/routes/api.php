<?php

use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(base_path('routes/auth.php'));

Route::prefix('cargo')->group(base_path('routes/cargo.php'));

Route::prefix('feriado')->group(base_path('routes/feriado.php'));

Route::prefix('rol')->group(base_path('routes/rol.php'));

Route::prefix('privilegio')->group(base_path('routes/privilegio.php'));

Route::middleware('jwt.auth')->group(function () {
    // Other protected routes
});
