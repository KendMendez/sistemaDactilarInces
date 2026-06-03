<?php

namespace App\Services;

use App\Models\Horario;
use Illuminate\Support\Facades\Crypt;

class HorarioService
{
    public function index()
    {
        $horarios = Horario::with('empleado:id,nombre,apellido')->orderBy('id', 'desc')->get()->map(function ($horarioTemp) {
            $cryptedId = Crypt::encrypt($horarioTemp->id);
            $horarioTemp->horarioId = $cryptedId;
            $horarioTemp->id_empleado = Crypt::encrypt($horarioTemp->id_empleado);
            unset($horarioTemp->id);

            return $horarioTemp;
        });

        return $horarios;
    }
    public function store(array $horario)
    {
        if (isset($horario['id_empleado'])) {
            $horario['id_empleado'] = Crypt::decrypt($horario['id_empleado']);
        }

        $exists = Horario::where('id_empleado', $horario['id_empleado'])
            ->where(function ($q) use ($horario) {
                foreach ($horario['dia'] as $day) {
                    $q->orWhereJsonContains('dia', $day);
                }
            })
            ->exists();

        if ($exists) {
            return false;
        }

        return Horario::create($horario);
    }

    public function update(string $id, array $horario)
    {
        $decryptedId = Crypt::decrypt($id);

        if (isset($horario['id_empleado'])) {
            $horario['id_empleado'] = Crypt::decrypt($horario['id_empleado']);
        }

        $findHorario = Horario::where('id_empleado', $horario['id_empleado'])
            ->where('id', '!=', $decryptedId)
            ->where(function ($q) use ($horario) {
                foreach ($horario['dia'] as $day) {
                    $q->orWhereJsonContains('dia', $day);
                }
            })
            ->first();

        if ($findHorario) {
            return false;
        }

        Horario::where('id', '=', $decryptedId)->update($horario);

        return true;
    }

    public function delete(string $id)
    {
        $decryptedId = Crypt::decrypt($id);
        Horario::where('id', '=', $decryptedId)->delete();

        return true;
    }
}
