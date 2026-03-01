<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    protected $fillable = [
        'id_empleado',
        'fecha',
        'hora_entrada',
        'hora_salida'
    ];
}
