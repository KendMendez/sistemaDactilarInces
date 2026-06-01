<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asistencia extends Model
{
    protected $fillable = [
        'id_empleado',
        'fecha',
        'hora_entrada',
        'hora_salida',
        'status',
        'tipo_marcacion',
    ];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
        'status' => 'string',
        'tipo_marcacion' => 'string',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'id_empleado');
    }
}
