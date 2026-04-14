<?php

namespace App\Services;

use App\Models\Cargo;
use Illuminate\Support\Facades\Crypt;

class CargoService
{
    public function index()
    {
        $cargos = Cargo::orderBy('cargo')->get()->map(function ($cargoTemp) {
            $cryptedId = Crypt::encrypt($cargoTemp->id);
            $cargoTemp->cargoId = $cryptedId;
            unset($cargoTemp->id);

            return $cargoTemp;
        });

        return $cargos;
    }

    public function showById(string $id)
    {
        $decryptedId = Crypt::decrypt($id);

        $findCargo = Cargo::select('id', 'cargo')->where('id', '=', $decryptedId)->first();
        $cryptedId = Crypt::encrypt($findCargo->id);
        $findCargo->cargoId = $cryptedId;
        unset($findCargo->id);

        return $findCargo;
    }

    public function store(array $cargo)
    {
        $exists = Cargo::where('cargo', $cargo['cargo'])->exists();

        if ($exists) {
            return false;
        } else {
            return Cargo::create($cargo);
        }
    }

    public function update(string $id, array $cargo)
    {
        $decryptedId = Crypt::decrypt($id);
        $findCargo = Cargo::select('id')->where([
            ['cargo', '=', $cargo['cargo']],
            ['id', '!=', $decryptedId],
        ])->first();

        if (! $findCargo) {
            Cargo::where('id', '=', $decryptedId)->update(['cargo' => $cargo['cargo']]);

            return true;
        } else {
            return false;
        }
    }

    public function delete(string $id)
    {
        $decryptedId = Crypt::decrypt($id);
        Cargo::where('id', '=', $decryptedId)->delete();

        return true;
    }
}
