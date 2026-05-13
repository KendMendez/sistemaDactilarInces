<?php

use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(base_path('routes/auth.php'));

Route::middleware('jwt.auth')->group(function () {
    Route::prefix('cargo')->group(base_path('routes/cargo.php'));

    Route::prefix('feriado')->group(base_path('routes/feriado.php'));

    Route::prefix('rol')->group(base_path('routes/rol.php'));

    Route::prefix('privilegio')->group(base_path('routes/privilegio.php'));

    Route::prefix('role-privilegio')->group(base_path('routes/role-privilegio.php'));

    Route::prefix('empleado')->group(base_path('routes/empleado.php'));

    Route::prefix('asistencia')->group(base_path('routes/asistencia.php'));

    Route::prefix('horario')->group(base_path('routes/horario.php'));

    Route::prefix('inasistencia')->group(base_path('routes/inasistencia.php'));
});
