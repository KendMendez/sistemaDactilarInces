<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    protected $fillable = [
        'id_empleado',
        'dia',
        'hora_entrada_esperada',
        'hora_salida_esperada',
    ];
}
