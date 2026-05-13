<?php

use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(base_path('routes/auth.php'));

Route::middleware(['jwt.auth', 'role:Administrador'])->prefix('cargo')->group(base_path('routes/cargo.php'));

Route::middleware(['jwt.auth', 'privilegio:view_holidays'])->prefix('feriado')->group(base_path('routes/feriado.php'));

Route::middleware(['jwt.auth', 'role:Administrador'])->prefix('rol')->group(base_path('routes/rol.php'));

Route::middleware(['jwt.auth', 'role:Administrador'])->prefix('privilegio')->group(base_path('routes/privilegio.php'));

Route::middleware(['jwt.auth', 'role:Administrador'])->prefix('role-privilegio')->group(base_path('routes/role-privilegio.php'));

Route::middleware(['jwt.auth', 'privilegio:view_employees'])->prefix('empleado')->group(base_path('routes/empleado.php'));

Route::middleware(['jwt.auth', 'privilegio:view_attendance'])->prefix('asistencia')->group(base_path('routes/asistencia.php'));

Route::middleware(['jwt.auth', 'privilegio:view_schedules'])->prefix('horario')->group(base_path('routes/horario.php'));

Route::middleware(['jwt.auth', 'privilegio:view_absences'])->prefix('inasistencia')->group(base_path('routes/inasistencia.php'));
