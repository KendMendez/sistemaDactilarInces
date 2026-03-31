<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inasistencia extends Model
{
    protected $fillable = [
        'id_empleado',
        'fecha',
        'justificacion',
    ];
}
