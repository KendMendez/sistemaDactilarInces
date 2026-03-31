<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePrivilegio extends Model
{
    protected $fillable = [
        'id_role',
        'id_privilegio',
    ];
}
