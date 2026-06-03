<?php

namespace App\Services;

use App\Models\Inasistencia;
use Illuminate\Support\Facades\Crypt;

class InasistenciaService
{
    public function index()
    {
        $inasistencias = Inasistencia::with('empleado:id,nombre,apellido')
            ->orderBy('fecha', 'desc')
            ->get()
            ->map(function ($inasistenciaTemp) {
                $cryptedId = Crypt::encrypt($inasistenciaTemp->id);
                $inasistenciaTemp->inasistenciaId = $cryptedId;
                $inasistenciaTemp->id_empleado = Crypt::encrypt($inasistenciaTemp->id_empleado);
                unset($inasistenciaTemp->id);

                return $inasistenciaTemp;
            });

        return $inasistencias;
    }
    public function store(array $inasistencia)
    {
        if (isset($inasistencia['id_empleado'])) {
            $inasistencia['id_empleado'] = Crypt::decrypt($inasistencia['id_empleado']);
        }

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

        if (isset($inasistencia['id_empleado'])) {
            $inasistencia['id_empleado'] = Crypt::decrypt($inasistencia['id_empleado']);
        }

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
