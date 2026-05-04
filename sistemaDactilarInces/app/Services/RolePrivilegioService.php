<?php

namespace App\Services;

use App\Models\RolePrivilegio;
use Illuminate\Support\Facades\Crypt;

class RolePrivilegioService
{
    public function findPrivilegiosByRoleId(string $roleId)
    {
        $decryptedRoleId = Crypt::decrypt($roleId);

        $results = RolePrivilegio::select(
            'role_privilegios.id as rolePrivilegioId',
            'roles.id as roleId',
            'privilegios.id as privilegioId',
            'privilegio'
        )
            ->join('roles', 'roles.id', '=', 'role_privilegios.id_role')
            ->join('privilegios', 'privilegios.id', '=', 'role_privilegios.id_privilegio')
            ->where('id_role', '=', $decryptedRoleId)
            ->get()
            ->map(function ($rolePrivilegioTemp) {
                $privilegioId = Crypt::encrypt($rolePrivilegioTemp->privilegioId);
                $roleId = Crypt::encrypt($rolePrivilegioTemp->roleId);
                $rolePrivilegioId = Crypt::encrypt($rolePrivilegioTemp->rolePrivilegioId);

                unset($rolePrivilegioTemp->privilegioId, $rolePrivilegioTemp->roleId, $rolePrivilegioTemp->rolePrivilegioId);

                $rolePrivilegioTemp->privilegioId = $privilegioId;
                $rolePrivilegioTemp->roleId = $roleId;
                $rolePrivilegioTemp->rolePrivilegioId = $rolePrivilegioId;

                return $rolePrivilegioTemp;
            })->toArray();

        return $results;
    }

    public function store(array $rolePrivilegio)
    {
        $decryptedRoleId = Crypt::decrypt($rolePrivilegio['roleId']);
        $arrPrivilegioId = json_decode($rolePrivilegio['arrPrivilegioId']);

        RolePrivilegio::where('id_role', '=', $decryptedRoleId)->delete();

        $arrInsert = [];
        for ($i = 0; $i < count($arrPrivilegioId); $i++) {
            $decryptedPrivilegioId = Crypt::decrypt($arrPrivilegioId[$i]);
            $arrInsert[] = [
                'id_role' => $decryptedRoleId,
                'id_privilegio' => $decryptedPrivilegioId,
            ];
        }

        RolePrivilegio::insert($arrInsert);

        return true;
    }
}
