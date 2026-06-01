<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inasistencia extends Model
{
    protected $fillable = [
        'id_empleado',
        'fecha',
        'justificacion',
    ];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'id_empleado');
    }
}
