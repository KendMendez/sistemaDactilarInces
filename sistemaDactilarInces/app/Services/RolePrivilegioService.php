<?php

namespace App\Services;

use App\Models\RolePrivilegio;
use Illuminate\Support\Facades\Crypt;

class RolePrivilegioService
{
    public function findPrivilegiosByRoleId(string $roleId)
    {
        $decryptedRoleId = Crypt::decrypt($roleId);

        $assignedIds = RolePrivilegio::where('id_role', $decryptedRoleId)
            ->pluck('id_privilegio')
            ->toArray();

        $all = \App\Models\Privilegio::orderBy('privilegio')->get()->map(function ($p) use ($assignedIds) {
            return [
                'privilegioId' => Crypt::encrypt($p->id),
                'privilegio'   => $p->privilegio,
                'selected'     => in_array($p->id, $assignedIds),
            ];
        })->toArray();

        return $all;
    }

    public function store(array $rolePrivilegio)
    {
        $decryptedRoleId = Crypt::decrypt($rolePrivilegio['roleId']);
        $arrPrivilegioId = is_array($rolePrivilegio['arrPrivilegioId'])
            ? $rolePrivilegio['arrPrivilegioId']
            : json_decode($rolePrivilegio['arrPrivilegioId']);

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
