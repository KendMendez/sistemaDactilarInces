<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Empleado extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'id_cargo',
        'nombre',
        'apellido',
        'telefono',
        'identificacion',
        'correo',
        'contraseña',
        'foto',
        'sexo',
        'huella_pulgar',
        'huella_indice',
    ];

    public function getAuthPassword()
    {
        return $this->contraseña;
    }

    public function getAuthIdentifierName()
    {
        return 'correo';
    }
}
