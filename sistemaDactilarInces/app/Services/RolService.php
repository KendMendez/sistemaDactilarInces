<?php

namespace App\Services;

use App\Models\Role;
use App\Models\RoleEmpleado;
use App\Models\RolePrivilegio;
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
    public function store(array $rol)
    {
        $exists = Role::where('role', $rol['role'])->exists();

        if ($exists) {
            return false;
        }

        $role = Role::create($rol);
        $cryptedId = Crypt::encrypt($role->id);
        $role->rolId = $cryptedId;
        unset($role->id);

        return $role;
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

    public function delete(string $id, bool $force = false)
    {
        $decryptedId = Crypt::decrypt($id);

        $empleadosCount = RoleEmpleado::where('id_role', $decryptedId)->count();
        $privilegiosCount = RolePrivilegio::where('id_role', $decryptedId)->count();

        if (($empleadosCount > 0 || $privilegiosCount > 0) && ! $force) {
            $parts = [];
            if ($empleadosCount > 0) {
                $parts[] = $empleadosCount . ' empleado(s) asignado(s)';
            }
            if ($privilegiosCount > 0) {
                $parts[] = $privilegiosCount . ' privilegio(s) asociado(s)';
            }

            return [
                'requires_confirmation' => true,
                'msg' => 'Este rol tiene ' . implode(' y ', $parts) . '. ¿Está seguro de eliminarlo?',
                'dependencies' => [
                    'empleados' => $empleadosCount,
                    'privilegios' => $privilegiosCount,
                ],
            ];
        }

        RolePrivilegio::where('id_role', $decryptedId)->delete();
        RoleEmpleado::where('id_role', $decryptedId)->delete();
        Role::where('id', $decryptedId)->delete();

        return true;
    }
}
