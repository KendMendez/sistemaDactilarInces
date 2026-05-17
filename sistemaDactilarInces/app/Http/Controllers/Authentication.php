<?php

namespace App\Http\Controllers;

use App\Helpers\Message;
use App\Services\EmpleadoAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Authentication extends Controller
{
    public function __construct(protected EmpleadoAuthService $authService) {}

    public function login(Request $req): JsonResponse
    {
        try {
            $correo = $req->input('correo');
            $contrasena = $req->input('contraseña');

            $authenticated = $this->authService->login([
                'correo' => $correo,
                'contraseña' => $contrasena,
            ]);

            if (! $authenticated) {
                $this->authService->registerAttempt($correo, $req, false);

                return response()->json([
                    'error' => 1,
                    'msg' => 'Correo o contraseña incorrectos',
                ], 401);
            }

            $this->authService->registerAttempt($correo, $req, true, $authenticated['empleado'] ?? null);

            $cookie = $this->authService->createSessionCookie($authenticated['token']);

            return response()->json([
                'error' => 0,
                'msg' => 'Inicio de sesión exitoso',
                'results' => [
                    'empleado' => $authenticated['empleado'] ?? null,
                ],
            ], 200)->withCookie($cookie);

        } catch (\Exception $e) {
            $this->authService->registerAttempt($req->input('correo'), $req, false);

            return response()->json([
                'error' => 1,
                'msg' => Message::exception(),
            ], 500);
        }
    }

    public function logout(): JsonResponse
    {
        $cookie = $this->authService->deleteSessionCookie();

        return response()->json([
            'error' => 0,
            'msg' => 'Sesión cerrada correctamente',
        ], 200)->withCookie($cookie);
    }
}
