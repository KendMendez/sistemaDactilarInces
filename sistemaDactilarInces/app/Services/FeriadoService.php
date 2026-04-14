<?php

namespace App\Services;

use App\Models\Feriado;
use Illuminate\Support\Facades\Crypt;

class FeriadoService
{
    public function index()
    {
        $feriados = Feriado::orderBy('fecha')->get()->map(function ($feriadoTemp) {
            $cryptedId = Crypt::encrypt($feriadoTemp->id);
            $feriadoTemp->feriadoId = $cryptedId;
            unset($feriadoTemp->id);

            return $feriadoTemp;
        });

        return $feriados;
    }

    public function showById(string $id)
    {
        $decryptedId = Crypt::decrypt($id);

        $findFeriado = Feriado::select('id', 'fecha', 'descripcion')->where('id', '=', $decryptedId)->first();
        $cryptedId = Crypt::encrypt($findFeriado->id);
        $findFeriado->feriadoId = $cryptedId;
        unset($findFeriado->id);

        return $findFeriado;
    }

    public function store(array $feriado)
    {
        $exists = Feriado::where('fecha', $feriado['fecha'])->exists();

        if ($exists) {
            return false;
        } else {
            return Feriado::create($feriado);
        }
    }

    public function update(string $id, array $feriado)
    {
        $decryptedId = Crypt::decrypt($id);
        $findFeriado = Feriado::select('id')->where([
            ['fecha', '=', $feriado['fecha']],
            ['id', '!=', $decryptedId],
        ])->first();

        if (! $findFeriado) {
            Feriado::where('id', '=', $decryptedId)->update([
                'fecha' => $feriado['fecha'],
                'descripcion' => $feriado['descripcion'],
            ]);

            return true;
        } else {
            return false;
        }
    }

    public function delete(string $id)
    {
        $decryptedId = Crypt::decrypt($id);
        Feriado::where('id', '=', $decryptedId)->delete();

        return true;
    }
}
