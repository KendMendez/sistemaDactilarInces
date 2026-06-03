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
        $empleados = Empleado::with('cargo:id,cargo')
            ->with('roles:id,role')
            ->orderBy('nombre')
            ->get()
            ->map(function ($empleadoTemp) {
                $cryptedId = Crypt::encrypt($empleadoTemp->id);
                $empleadoTemp->empleadoId = $cryptedId;

                if ($empleadoTemp->id_cargo) {
                    $empleadoTemp->id_cargo = Crypt::encrypt($empleadoTemp->id_cargo);
                }

                $empleadoTemp->rolIds = $empleadoTemp->roles->map(fn($r) => Crypt::encrypt($r->id))->values();
                unset($empleadoTemp->roles);

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

    public function findByIdentificacion(string $identificacion)
    {
        $findEmpleado = Empleado::with('cargo:id,cargo')
            ->with('roles:id,role')
            ->where('identificacion', $identificacion)
            ->first();

        if (! $findEmpleado) {
            return null;
        }

        $cryptedId = Crypt::encrypt($findEmpleado->id);
        $findEmpleado->empleadoId = $cryptedId;

        if ($findEmpleado->id_cargo) {
            $findEmpleado->id_cargo = Crypt::encrypt($findEmpleado->id_cargo);
        }

        $findEmpleado->rolIds = $findEmpleado->roles->map(fn($r) => Crypt::encrypt($r->id))->values();
        unset($findEmpleado->roles);
        unset($findEmpleado->id);
        unset($findEmpleado->contraseña);
        unset($findEmpleado->huella_pulgar);
        unset($findEmpleado->huella_indice);

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
                return false;
            }
        }

        if (isset($empleado['contraseña']) && ! empty($empleado['contraseña'])) {
            $empleado['contraseña'] = Hash::make($empleado['contraseña']);
        }

        if (isset($empleado['foto']) && ! empty($empleado['foto'])) {
            $this->validateBase64Image($empleado['foto']);
        }

        if (isset($empleado['huella_pulgar']) && ! empty($empleado['huella_pulgar'])) {
            $this->validateHuellaBase64($empleado['huella_pulgar']);
        }
        if (isset($empleado['huella_indice']) && ! empty($empleado['huella_indice'])) {
            $this->validateHuellaBase64($empleado['huella_indice']);
        }

        if (isset($empleado['id_cargo'])) {
            $empleado['id_cargo'] = Crypt::decrypt($empleado['id_cargo']);
        }

        $roleId = $empleado['roleId'] ?? null;
        unset($empleado['roleId']);
        $createdEmpleado = Empleado::create($empleado);

        if (! empty($roleId) && $roleId !== '[]') {
            $this->assignRoles($createdEmpleado->id, $roleId);
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
            $this->validateHuellaBase64($empleado['huella_pulgar']);
        }
        if (isset($empleado['huella_indice']) && ! empty($empleado['huella_indice'])) {
            $this->validateHuellaBase64($empleado['huella_indice']);
        }

        if (isset($empleado['id_cargo'])) {
            $empleado['id_cargo'] = Crypt::decrypt($empleado['id_cargo']);
        }

        $roleId = $empleado['roleId'] ?? null;
        unset($empleado['roleId']);
        Empleado::where('id', '=', $decryptedId)->update($empleado);

        if (! empty($roleId) && $roleId !== '[]') {
            $this->assignRoles($decryptedId, $roleId);
        }

        return true;
    }

    public function delete(string $id, bool $force = false)
    {
        $decryptedId = Crypt::decrypt($id);

        $rolesCount = RoleEmpleado::where('id_empleado', $decryptedId)->count();
        $asistenciasCount = \App\Models\Asistencia::where('id_empleado', $decryptedId)->count();
        $inasistenciasCount = \App\Models\Inasistencia::where('id_empleado', $decryptedId)->count();
        $horariosCount = \App\Models\Horario::where('id_empleado', $decryptedId)->count();

        $parts = [];
        if ($rolesCount > 0) {
            $parts[] = $rolesCount . ' role(s) asignado(s)';
        }
        if ($asistenciasCount > 0) {
            $parts[] = $asistenciasCount . ' asistencia(s) registrada(s)';
        }
        if ($inasistenciasCount > 0) {
            $parts[] = $inasistenciasCount . ' inasistencia(s) registrada(s)';
        }
        if ($horariosCount > 0) {
            $parts[] = $horariosCount . ' horario(s) asignado(s)';
        }

        if (count($parts) > 0 && ! $force) {
            return [
                'requires_confirmation' => true,
                'msg' => 'Este empleado tiene ' . implode(', ', $parts) . '. ¿Está seguro de eliminarlo?',
                'dependencies' => [
                    'roles' => $rolesCount,
                    'asistencias' => $asistenciasCount,
                    'inasistencias' => $inasistenciasCount,
                    'horarios' => $horariosCount,
                ],
            ];
        }

        RoleEmpleado::where('id_empleado', $decryptedId)->delete();
        \App\Models\Asistencia::where('id_empleado', $decryptedId)->delete();
        \App\Models\Inasistencia::where('id_empleado', $decryptedId)->delete();
        \App\Models\Horario::where('id_empleado', $decryptedId)->delete();
        Empleado::where('id', $decryptedId)->delete();

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

    private function validateBase64Image(string $base64, int $maxKB = 2048): void
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

    private function validateHuellaBase64(string $value): void
    {
        if (empty($value)) {
            throw new \Exception('La huella no puede estar vacía');
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
