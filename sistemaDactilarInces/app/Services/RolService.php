<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Support\Facades\Crypt;

class RolService
{
    public function index()
    {
        $roles = Role::orderBy('role')->get()->map(function ($rolTemp) {
            $cryptedId = Crypt::encrypt($rolTemp->id);
            $rolTemp->rolId = $cryptedId;
            unset($rolTemp->id);

            return $rolTemp;
        });

        return $roles;
    }

    public function showById(string $id)
    {
        $decryptedId = Crypt::decrypt($id);

        $findRol = Role::select('id', 'role')->where('id', '=', $decryptedId)->first();
        $cryptedId = Crypt::encrypt($findRol->id);
        $findRol->rolId = $cryptedId;
        unset($findRol->id);

        return $findRol;
    }

    public function store(array $rol)
    {
        $exists = Role::where('role', $rol['role'])->exists();

        if ($exists) {
            return false;
        } else {
            return Role::create($rol);
        }
    }

    public function update(string $id, array $rol)
    {
        $decryptedId = Crypt::decrypt($id);
        $findRol = Role::select('id')->where([
            ['role', '=', $rol['role']],
            ['id', '!=', $decryptedId],
        ])->first();

        if (! $findRol) {
            Role::where('id', '=', $decryptedId)->update(['role' => $rol['role']]);

            return true;
        } else {
            return false;
        }
    }

    public function delete(string $id)
    {
        $decryptedId = Crypt::decrypt($id);
        Role::where('id', '=', $decryptedId)->delete();

        return true;
    }
}
