<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Privilegio extends Model
{
    protected $fillable = [
        'privilegio',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_privilegios', 'id_privilegio', 'id_role');
    }
}
