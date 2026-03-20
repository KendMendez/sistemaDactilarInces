<?php

namespace App\Services;

use App\Models\Empleado;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
class EmpleadoAuthService
{
    public function login(array $auth)
    {
        $msg = 'Bienvenido';
        $error = 0;
        $key = 'CpWX/5xZ/47HfkPCC4eOKoIG/NA6EZWb3ps67fYyVY0=';

        $time = time();
        $sessionTime = (60 * 60);
        $sessionExpired = $time + $sessionTime;

        $findEmpleado = Empleado::select('nombre', 'apellido', 'contraseña')->where([
            ['correo', '=', $auth['correo']],
        ])->first();

        $payload = [];

        if ($findEmpleado && Hash::check($auth['contraseña'], $findEmpleado['contraseña'])) {
            $token = JWT::encode(['user' => $findEmpleado], $key, 'HS256');
            $payload = [
                'iat' => $time,
                'expired' => $sessionExpired,
                'token' => $token,
                'msg' => $msg,
                'error' => $error,
                'empleado' => [
                    'nombre' => $findEmpleado['nombre'],
                    'apellido' => $findEmpleado['apellido']
                ]
            ];
        }
        return $payload;
    }
}
