<?php

use App\Http\Controllers\PrivilegioController;
use Illuminate\Support\Facades\Route;

Route::get('/index', [PrivilegioController::class, 'index'])->middleware('privilegio:ver privilegios');

// Route::post('/store', [PrivilegioController::class, 'store']);
// Route::put('/update/{id}', [PrivilegioController::class, 'update']);

Route::delete('/{id}/delete', [PrivilegioController::class, 'delete'])->middleware('privilegio:eliminar privilegio');
