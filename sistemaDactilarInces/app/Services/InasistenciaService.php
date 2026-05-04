<?php

namespace App\Services;

use App\Models\Inasistencia;
use Illuminate\Support\Facades\Crypt;

class InasistenciaService
{
    public function index()
    {
        $inasistencias = Inasistencia::orderBy('fecha', 'desc')->get()->map(function ($inasistenciaTemp) {
            $cryptedId = Crypt::encrypt($inasistenciaTemp->id);
            $inasistenciaTemp->inasistenciaId = $cryptedId;
            unset($inasistenciaTemp->id);

            return $inasistenciaTemp;
        });

        return $inasistencias;
    }

    public function showById(string $id)
    {
        $decryptedId = Crypt::decrypt($id);

        $findInasistencia = Inasistencia::select('id', 'id_empleado', 'fecha', 'justificacion')
            ->where('id', '=', $decryptedId)
            ->first();

        if (! $findInasistencia) {
            throw new \Exception('Inasistencia no encontrada');
        }

        $cryptedId = Crypt::encrypt($findInasistencia->id);
        $findInasistencia->inasistenciaId = $cryptedId;
        unset($findInasistencia->id);

        return $findInasistencia;
    }

    public function store(array $inasistencia)
    {
        $exists = Inasistencia::where([
            ['id_empleado', '=', $inasistencia['id_empleado']],
            ['fecha', '=', $inasistencia['fecha']],
        ])->exists();

        if ($exists) {
            return false;
        }

        return Inasistencia::create($inasistencia);
    }

    public function update(string $id, array $inasistencia)
    {
        $decryptedId = Crypt::decrypt($id);

        $findInasistencia = Inasistencia::select('id')->where([
            ['id_empleado', '=', $inasistencia['id_empleado']],
            ['fecha', '=', $inasistencia['fecha']],
            ['id', '!=', $decryptedId],
        ])->first();

        if ($findInasistencia) {
            return false;
        }

        Inasistencia::where('id', '=', $decryptedId)->update($inasistencia);

        return true;
    }

    public function delete(string $id)
    {
        $decryptedId = Crypt::decrypt($id);
        Inasistencia::where('id', '=', $decryptedId)->delete();

        return true;
    }
}
