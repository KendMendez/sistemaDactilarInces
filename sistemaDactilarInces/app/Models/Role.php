<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = [
        'role',
    ];

    public function empleados(): BelongsToMany
    {
        return $this->belongsToMany(Empleado::class, 'role_empleados', 'id_role', 'id_empleado');
    }

    public function privilegios(): BelongsToMany
    {
        return $this->belongsToMany(Privilegio::class, 'role_privilegios', 'id_role', 'id_privilegio');
    }
}
