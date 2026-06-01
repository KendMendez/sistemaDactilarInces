<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Horario extends Model
{
    protected $fillable = [
        'id_empleado',
        'dia',
        'hora_entrada_esperada',
        'hora_salida_esperada',
    ];

    protected $casts = [
        'hora_entrada_esperada' => 'string',
        'hora_salida_esperada' => 'string',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'id_empleado');
    }
}
