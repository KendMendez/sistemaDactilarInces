<?php

namespace App\Services;

use App\Models\Feriado;
use Carbon\Carbon;
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
        $fechaInput = $feriado['fecha'];

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInput)) {
            $fechaParsed = $fechaInput;
        } else {
            $fechaParsed = Carbon::createFromFormat('d/m/Y', $fechaInput)->format('Y-m-d');
        }

        $exists = Feriado::where('fecha', $fechaParsed)->exists();

        if ($exists) {
            return false;
        } else {
            return Feriado::create([
                'fecha' => $fechaParsed,
                'descripcion' => $feriado['descripcion'],
            ]);
        }
    }

    public function update(string $id, array $feriado)
    {
        $decryptedId = Crypt::decrypt($id);
        $fechaInput = $feriado['fecha'];

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInput)) {
            $fechaParsed = $fechaInput;
        } else {
            $fechaParsed = Carbon::createFromFormat('d/m/Y', $fechaInput)->format('Y-m-d');
        }

        $findFeriado = Feriado::select('id')->where([
            ['fecha', '=', $fechaParsed],
            ['id', '!=', $decryptedId],
        ])->first();

        if (! $findFeriado) {
            Feriado::where('id', '=', $decryptedId)->update([
                'fecha' => $fechaParsed,
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
