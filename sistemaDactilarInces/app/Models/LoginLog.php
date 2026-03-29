<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    protected $table = 'login_logs';

    protected $fillable = [
        'id_empleado',
        'correo',
        'ip',
        'user_agent',
        'exito',
    ];

    protected $casts = [
        'exito' => 'boolean',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado');
    }
}
