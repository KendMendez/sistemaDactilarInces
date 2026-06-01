<?php

use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('auth.')->group(base_path('routes/auth.php'));

Route::prefix('kiosko')->name('kiosko.')->group(base_path('routes/kiosko.php'));

Route::middleware('jwt.auth')->group(function () {

    Route::prefix('cargo')->name('cargo.')->group(base_path('routes/cargo.php'));
    
    Route::prefix('feriado')->name('feriado.')->group(base_path('routes/feriado.php'));
    
    Route::prefix('rol')->name('rol.')->group(base_path('routes/rol.php'));
    
    Route::prefix('privilegio')->name('privilegio.')->group(base_path('routes/privilegio.php'));
    
    Route::prefix('role-privilegio')->name('role-privilegio.')->group(base_path('routes/role-privilegio.php'));
    
    Route::prefix('empleado')->name('empleado.')->group(base_path('routes/empleado.php'));
    
    Route::prefix('asistencia')->name('asistencia.')->group(base_path('routes/asistencia.php'));
    
    Route::prefix('horario')->name('horario.')->group(base_path('routes/horario.php'));
    
    Route::prefix('inasistencia')->name('inasistencia.')->group(base_path('routes/inasistencia.php'));
});
