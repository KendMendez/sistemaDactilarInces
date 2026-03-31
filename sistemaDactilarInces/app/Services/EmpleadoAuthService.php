<?php

namespace App\Services;

use App\Mail\ResetPasswordMail;
use App\Models\Empleado;
use App\Models\LoginLog;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;

class EmpleadoAuthService
{
    public function login(array $auth)
    {
        $message = 'Bienvenido';
        $errorCode = 0;
        $key = env('JWT_SECRET');

        $time = time();
        $sessionTime = (60 * 60);
        $sessionExpired = $time + $sessionTime;

        $foundEmployee = Empleado::select('id', 'nombre', 'apellido', 'contraseña')->where([
            ['correo', '=', $auth['correo']],
        ])->first();

        $response = [];

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

    public function sendResetLink(string $correo): array
    {
        $empleado = Empleado::where('correo', $correo)->first();

        if (! $empleado) {
            return [
                'error' => 1,
                'msg' => 'No se encontró un usuario con ese correo electrónico.',
            ];
        }

        $token = Str::random(64);
        $expiresAt = Carbon::now()->addHours(1);

        $empleado->reset_token = hash('sha256', $token);
        $empleado->reset_token_expira = $expiresAt;
        $empleado->save();

        Mail::to($empleado->correo)->send(new ResetPasswordMail($empleado, $token));

        return [
            'error' => 0,
            'msg' => 'Se ha enviado un enlace de recuperación a tu correo.',
        ];
    }

    public function resetPassword(string $token, string $contrasena): array
    {
        $hashedToken = hash('sha256', $token);

        $empleado = Empleado::where('reset_token', $hashedToken)->first();

        if (! $empleado) {
            return [
                'error' => 1,
                'msg' => 'Token de recuperación inválido.',
            ];
        }

        if (Carbon::now()->greaterThan($empleado->reset_token_expira)) {
            return [
                'error' => 1,
                'msg' => 'El enlace de recuperación ha expirado.',
            ];
        }

        $empleado->contraseña = bcrypt($contrasena);
        $empleado->reset_token = null;
        $empleado->reset_token_expira = null;
        $empleado->save();

        return [
            'error' => 0,
            'msg' => 'Contraseña actualizada correctamente.',
        ];
    }
}
