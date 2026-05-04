<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\RoleEmpleado;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class EmpleadoService
{
    public function index()
    {
        $empleados = Empleado::orderBy('nombre')->get()->map(function ($empleadoTemp) {
            $cryptedId = Crypt::encrypt($empleadoTemp->id);
            $empleadoTemp->empleadoId = $cryptedId;
            unset(
                $empleadoTemp->id,
                $empleadoTemp->contraseña,
                $empleadoTemp->huella_pulgar,
                $empleadoTemp->huella_indice
            );

            return $empleadoTemp;
        });

        return $empleados;
    }

    public function showById(string $id)
    {
        $decryptedId = Crypt::decrypt($id);

        $findEmpleado = Empleado::select(
            'id',
            'id_cargo',
            'nombre',
            'apellido',
            'telefono',
            'identificacion',
            'correo',
            'foto',
            'sexo'
        )->where('id', '=', $decryptedId)->first();

        if (! $findEmpleado) {
            throw new \Exception('Empleado no encontrado');
        }

        $cryptedId = Crypt::encrypt($findEmpleado->id);
        $findEmpleado->empleadoId = $cryptedId;
        unset($findEmpleado->id);

        return $findEmpleado;
    }

    public function store(array $empleado)
    {
        if (isset($empleado['correo']) && ! empty($empleado['correo'])) {
            $exists = Empleado::where('correo', $empleado['correo'])->exists();
            if ($exists) {
                return false;
            }
        }

        if (isset($empleado['identificacion'])) {
            $exists = Empleado::where('identificacion', $empleado['identificacion'])->exists();
            if ($exists) {
                throw new \Exception('Identificacion duplicada');
            }
        }

        if (isset($empleado['contraseña']) && ! empty($empleado['contraseña'])) {
            $empleado['contraseña'] = Hash::make($empleado['contraseña']);
        }

        if (isset($empleado['foto']) && ! empty($empleado['foto'])) {
            $this->validateBase64Image($empleado['foto']);
        }

        if (isset($empleado['huella_pulgar']) && ! empty($empleado['huella_pulgar'])) {
            $this->validateBase64String($empleado['huella_pulgar']);
        }
        if (isset($empleado['huella_indice']) && ! empty($empleado['huella_indice'])) {
            $this->validateBase64String($empleado['huella_indice']);
        }

        $createdEmpleado = Empleado::create($empleado);

        if (isset($empleado['roleId'])) {
            $this->assignRoles($createdEmpleado->id, $empleado['roleId']);
        }

        return [
            'correo' => $createdEmpleado->correo,
            'nombre' => $createdEmpleado->nombre,
        ];
    }

    public function update(string $id, array $empleado)
    {
        $decryptedId = Crypt::decrypt($id);

        if (isset($empleado['correo']) && ! empty($empleado['correo'])) {
            $findEmpleado = Empleado::select('id')->where([
                ['correo', '=', $empleado['correo']],
                ['id', '!=', $decryptedId],
            ])->first();
            if ($findEmpleado) {
                return false;
            }
        }

        if (isset($empleado['identificacion'])) {
            $findEmpleado = Empleado::select('id')->where([
                ['identificacion', '=', $empleado['identificacion']],
                ['id', '!=', $decryptedId],
            ])->first();
            if ($findEmpleado) {
                throw new \Exception('Identificacion duplicada');
            }
        }

        if (isset($empleado['contraseña']) && ! empty($empleado['contraseña'])) {
            $empleado['contraseña'] = Hash::make($empleado['contraseña']);
        }

        if (isset($empleado['foto']) && ! empty($empleado['foto'])) {
            $this->validateBase64Image($empleado['foto']);
        }

        if (isset($empleado['huella_pulgar']) && ! empty($empleado['huella_pulgar'])) {
            $this->validateBase64String($empleado['huella_pulgar']);
        }
        if (isset($empleado['huella_indice']) && ! empty($empleado['huella_indice'])) {
            $this->validateBase64String($empleado['huella_indice']);
        }

        Empleado::where('id', '=', $decryptedId)->update($empleado);

        if (isset($empleado['roleId'])) {
            $this->assignRoles($decryptedId, $empleado['roleId']);
        }

        return true;
    }

    public function delete(string $id)
    {
        $decryptedId = Crypt::decrypt($id);

        RoleEmpleado::where('id_empleado', '=', $decryptedId)->delete();

        Empleado::where('id', '=', $decryptedId)->delete();

        return true;
    }

    private function assignRoles(int $empleadoId, string $roleIdJson)
    {
        $arrRoles = json_decode($roleIdJson);
        if (! is_array($arrRoles)) {
            throw new \Exception('roleId debe ser un array JSON válido');
        }

        RoleEmpleado::where('id_empleado', '=', $empleadoId)->delete();

        $arrInsert = [];
        foreach ($arrRoles as $roleIdEncrypted) {
            $roleId = Crypt::decrypt($roleIdEncrypted);
            $arrInsert[] = [
                'id_empleado' => $empleadoId,
                'id_role' => $roleId,
            ];
        }

        RoleEmpleado::insert($arrInsert);
    }

    private function validateBase64Image(string $base64, int $maxKB = 1000): void
    {
        $decoded = base64_decode($base64, true);
        if ($decoded === false) {
            throw new \Exception('El formato base64 de la imagen no es válido');
        }

        $sizeInKB = strlen($decoded) / 1024;
        if ($sizeInKB > $maxKB) {
            throw new \Exception("La imagen no debe pesar más de {$maxKB} KB");
        }

        // Validar magic bytes (JPEG, PNG, GIF)
        $magicBytes = substr($decoded, 0, 4);
        $isValidImage = false;

        // JPEG: FF D8 FF
        if (strpos($magicBytes, "\xFF\xD8\xFF") === 0) {
            $isValidImage = true;
        } elseif (strpos($magicBytes, "\x89\x50\x4E\x47") === 0) {
            $isValidImage = true;
        } elseif (strpos($magicBytes, 'GIF8') === 0) {
            $isValidImage = true;
        }

        if (! $isValidImage) {
            throw new \Exception('El archivo no es una imagen válida (JPEG, PNG, GIF)');
        }
    }

    private function validateBase64String(string $base64): void
    {
        $decoded = base64_decode($base64, true);
        if ($decoded === false) {
            throw new \Exception('El formato base64 no es válido');
        }
    }
}
