<?php

namespace App\Services;

use App\Models\Asistencia;
use Illuminate\Support\Facades\Crypt;

class AsistenciaService
{
    public function index()
    {
        $asistencias = Asistencia::with('empleado:id,nombre,apellido')
            ->orderBy('fecha', 'desc')
            ->get()
            ->map(function ($asistenciaTemp) {
                $cryptedId = Crypt::encrypt($asistenciaTemp->id);
                $asistenciaTemp->asistenciaId = $cryptedId;
                unset($asistenciaTemp->id);
                $asistenciaTemp->empleado_nombre = $asistenciaTemp->empleado?->nombre . ' ' . $asistenciaTemp->empleado?->apellido;
                unset($asistenciaTemp->empleado);

                return $asistenciaTemp;
            });

        return $asistencias;
    }
    public function store(array $asistencia)
    {
        if (isset($asistencia['id_empleado'])) {
            $asistencia['id_empleado'] = Crypt::decrypt($asistencia['id_empleado']);
        }

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

        if (isset($asistencia['id_empleado'])) {
            $asistencia['id_empleado'] = Crypt::decrypt($asistencia['id_empleado']);
        }

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

    public function pending()
    {
        $asistencias = Asistencia::with('empleado:id,nombre,apellido')
            ->where('status', 'pending_approval')
            ->orderBy('fecha', 'desc')
            ->get()
            ->map(function ($asistenciaTemp) {
                $cryptedId = Crypt::encrypt($asistenciaTemp->id);
                $asistenciaTemp->asistenciaId = $cryptedId;
                unset($asistenciaTemp->id);
                $asistenciaTemp->empleado_nombre = $asistenciaTemp->empleado?->nombre . ' ' . $asistenciaTemp->empleado?->apellido;
                unset($asistenciaTemp->empleado);

                return $asistenciaTemp;
            });

        return $asistencias;
    }

    public function approve(string $id): bool
    {
        $decryptedId = Crypt::decrypt($id);

        $asistencia = Asistencia::find($decryptedId);
        if (! $asistencia) {
            throw new \Exception('Asistencia no encontrada');
        }

        $asistencia->update(['status' => 'approved']);

        return true;
    }

    public function reject(string $id): bool
    {
        $decryptedId = Crypt::decrypt($id);

        $asistencia = Asistencia::find($decryptedId);
        if (! $asistencia) {
            throw new \Exception('Asistencia no encontrada');
        }

        $asistencia->update(['status' => 'rejected']);

        return true;
    }
}
