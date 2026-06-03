<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Horario extends Model
{
    protected $fillable = [
        'id_empleado',
        'dia',
        'hora_entrada',
        'hora_salida',
        'hora_entrada_tolerada',
        'hora_salida_tolerada',
    ];

    protected $casts = [
        'dia' => 'array',
        'hora_entrada' => 'string',
        'hora_salida' => 'string',
        'hora_entrada_tolerada' => 'string',
        'hora_salida_tolerada' => 'string',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'id_empleado');
    }
}
