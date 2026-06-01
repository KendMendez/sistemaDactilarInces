<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\LoginLog;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Cookie;

class EmpleadoAuthService
{
    public function login(array $auth)
    {
        $message = 'Bienvenido';
        $errorCode = 0;
        $key = config('jwt.secret');

        $time = time();
        $sessionTime = (60 * 60);
        $sessionExpired = $time + $sessionTime;

        $foundEmployee = Empleado::select('id', 'nombre', 'apellido', 'contraseña')->where([
            ['correo', '=', $auth['correo']],
        ])->first();

        Log::debug('[AuthService] Employee lookup', [
            'correo' => $auth['correo'],
            'found' => $foundEmployee ? 'yes' : 'no',
            'auth_contraseña' => $auth['contraseña'] ?? 'MISSING',
            'auth_contraseña_len' => is_string($auth['contraseña']) ? strlen($auth['contraseña']) : null,
        ]);

        $response = null;

        if ($foundEmployee && Hash::check($auth['contraseña'], $foundEmployee['contraseña'])) {
            $token = JWT::encode(['user' => $foundEmployee->id], $key, 'HS256');
            $response = [
                'iat' => $time,
                'expired' => $sessionExpired,
                'token' => $token,
                'msg' => $message,
                'error' => $errorCode,
                'empleado' => [
                    'nombre' => $foundEmployee['nombre'],
                    'apellido' => $foundEmployee['apellido'],
                ],
            ];
        }

        Log::debug('[AuthService] Login result', [
            'employee_found' => $foundEmployee ? 'yes' : 'no',
            'response_empty' => empty($response) ? 'yes' : 'no',
            'hash_check_called' => $foundEmployee ? 'yes' : 'N/A',
        ]);

        return $response;
    }

    public function registerAttempt(
        ?string $correo,
        Request $request,
        bool $exito,
        ?array $empleado = null
    ): void {
        LoginLog::create([
            'id_empleado' => $empleado['id'] ?? null,
            'correo' => $correo ?? 'desconocido',
            'ip' => $request->ip() ?? '0.0.0.0',
            'user_agent' => $request->userAgent(),
            'exito' => $exito,
        ]);
    }

    public function createSessionCookie(string $token): Cookie
    {
        return cookie(
            'token',
            $token,
            60,
            '/',
            null,
            false,
            true
        );
    }

    public function deleteSessionCookie(): Cookie
    {
        return cookie()->forget('token');
    }
}
