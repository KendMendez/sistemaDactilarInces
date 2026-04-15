<?php

namespace App\Services;

use App\Models\Privilegio;
use Illuminate\Support\Facades\Crypt;

class PrivilegioService
{
    public function index()
    {
        $privilegios = Privilegio::orderBy('privilegio')->get()->map(function ($privilegioTemp) {
            $cryptedId = Crypt::encrypt($privilegioTemp->id);
            $privilegioTemp->privilegioId = $cryptedId;
            unset($privilegioTemp->id);

            return $privilegioTemp;
        });

        return $privilegios;
    }

    public function showById(string $id)
    {
        $decryptedId = Crypt::decrypt($id);

        $findPrivilegio = Privilegio::select('id', 'privilegio')->where('id', '=', $decryptedId)->first();
        $cryptedId = Crypt::encrypt($findPrivilegio->id);
        $findPrivilegio->privilegioId = $cryptedId;
        unset($findPrivilegio->id);

        return $findPrivilegio;
    }

    public function store(array $privilegio)
    {
        $exists = Privilegio::where('privilegio', $privilegio['privilegio'])->exists();

        if ($exists) {
            return false;
        } else {
            return Privilegio::create($privilegio);
        }
    }

    public function update(string $id, array $privilegio)
    {
        $decryptedId = Crypt::decrypt($id);
        $findPrivilegio = Privilegio::select('id')->where([
            ['privilegio', '=', $privilegio['privilegio']],
            ['id', '!=', $decryptedId],
        ])->first();

        if (! $findPrivilegio) {
            Privilegio::where('id', '=', $decryptedId)->update(['privilegio' => $privilegio['privilegio']]);

            return true;
        } else {
            return false;
        }
    }

    public function delete(string $id)
    {
        $decryptedId = Crypt::decrypt($id);
        Privilegio::where('id', '=', $decryptedId)->delete();

        return true;
    }
}
