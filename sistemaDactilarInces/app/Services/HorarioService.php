<?php

namespace App\Services;

use App\Models\Horario;
use Illuminate\Support\Facades\Crypt;

class HorarioService
{
    public function index()
    {
        $horarios = Horario::orderBy('dia')->get()->map(function ($horarioTemp) {
            $cryptedId = Crypt::encrypt($horarioTemp->id);
            $horarioTemp->horarioId = $cryptedId;
            unset($horarioTemp->id);

            return $horarioTemp;
        });

        return $horarios;
    }

    public function showById(string $id)
    {
        $decryptedId = Crypt::decrypt($id);

        $findHorario = Horario::select('id', 'id_empleado', 'dia', 'hora_entrada_esperada', 'hora_salida_esperada')
            ->where('id', '=', $decryptedId)
            ->first();

        if (! $findHorario) {
            throw new \Exception('Horario no encontrado');
        }

        $cryptedId = Crypt::encrypt($findHorario->id);
        $findHorario->horarioId = $cryptedId;
        unset($findHorario->id);

        return $findHorario;
    }

    public function store(array $horario)
    {
        $exists = Horario::where([
            ['id_empleado', '=', $horario['id_empleado']],
            ['dia', '=', $horario['dia']],
        ])->exists();

        if ($exists) {
            return false;
        }

        return Horario::create($horario);
    }

    public function update(string $id, array $horario)
    {
        $decryptedId = Crypt::decrypt($id);

        $findHorario = Horario::select('id')->where([
            ['id_empleado', '=', $horario['id_empleado']],
            ['dia', '=', $horario['dia']],
            ['id', '!=', $decryptedId],
        ])->first();

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
