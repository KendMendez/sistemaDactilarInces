<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleEmpleado extends Model
{
    protected $fillable = [
        'id_empleado',
        'id_role',
    ];
}
