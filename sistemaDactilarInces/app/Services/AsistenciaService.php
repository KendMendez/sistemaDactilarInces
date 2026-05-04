<?php

namespace App\Services;

use App\Models\Asistencia;
use Illuminate\Support\Facades\Crypt;

class AsistenciaService
{
    public function index()
    {
        $asistencias = Asistencia::orderBy('fecha', 'desc')->get()->map(function ($asistenciaTemp) {
            $cryptedId = Crypt::encrypt($asistenciaTemp->id);
            $asistenciaTemp->asistenciaId = $cryptedId;
            unset($asistenciaTemp->id);

            return $asistenciaTemp;
        });

        return $asistencias;
    }

    public function showById(string $id)
    {
        $decryptedId = Crypt::decrypt($id);

        $findAsistencia = Asistencia::select('id', 'id_empleado', 'fecha', 'hora_entrada', 'hora_salida')
            ->where('id', '=', $decryptedId)
            ->first();

        if (! $findAsistencia) {
            throw new \Exception('Asistencia no encontrada');
        }

        $cryptedId = Crypt::encrypt($findAsistencia->id);
        $findAsistencia->asistenciaId = $cryptedId;
        unset($findAsistencia->id);

        return $findAsistencia;
    }

    public function store(array $asistencia)
    {
        $exists = Asistencia::where([
            ['id_empleado', '=', $asistencia['id_empleado']],
            ['fecha', '=', $asistencia['fecha']],
        ])->exists();

        if ($exists) {
            return false;
        }

        return Asistencia::create($asistencia);
    }

    public function update(string $id, array $asistencia)
    {
        $decryptedId = Crypt::decrypt($id);

        $findAsistencia = Asistencia::select('id')->where([
            ['id_empleado', '=', $asistencia['id_empleado']],
            ['fecha', '=', $asistencia['fecha']],
            ['id', '!=', $decryptedId],
        ])->first();

        if ($findAsistencia) {
            return false;
        }

        Asistencia::where('id', '=', $decryptedId)->update($asistencia);

        return true;
    }

    public function delete(string $id)
    {
        $decryptedId = Crypt::decrypt($id);
        Asistencia::where('id', '=', $decryptedId)->delete();

        return true;
    }
}
