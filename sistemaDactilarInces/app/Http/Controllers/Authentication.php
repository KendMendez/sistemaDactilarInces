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

            $logged = $this->authService->login([
                'correo' => $correo,
                'contraseña' => $contrasena,
            ]);

            if (! $logged) {
                $this->authService->registrarIntento($correo, $req, false);

                return response()->json([
                    'error' => 1,
                    'msg' => 'Correo o contraseña incorrectos',
                ], 401);
            }

            $this->authService->registrarIntento($correo, $req, true, $logged['empleado'] ?? null);

            $cookie = $this->authService->crearCookieSesion($logged['token']);

            return response()->json([
                'error' => 0,
                'msg' => 'Inicio de sesión exitoso',
                'results' => [
                    'empleado' => $logged['empleado'] ?? null,
                ],
            ], 200)->withCookie($cookie);

        } catch (\Exception $e) {
            $this->authService->registrarIntento($req->input('correo'), $req, false);

            return response()->json([
                'error' => 1,
                'msg' => Message::exception(),
            ], 500);
        }
    }

    public function logout(): JsonResponse
    {
        $cookie = $this->authService->eliminarCookieSesion();

        return response()->json([
            'error' => 0,
            'msg' => 'Sesión cerrada correctamente',
        ], 200)->withCookie($cookie);
    }

    public function forgotPassword(Request $req): JsonResponse
    {
        try {
            $correo = $req->input('correo');

            if (! $correo) {
                return response()->json([
                    'error' => 1,
                    'msg' => 'El correo es requerido',
                ], 400);
            }

            $result = $this->authService->sendResetLink($correo);

            $statusCode = $result['error'] === 0 ? 200 : 404;

            return response()->json($result, $statusCode);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 1,
                'msg' => Message::exception(),
            ], 500);
        }
    }

    public function resetPassword(Request $req): JsonResponse
    {
        try {
            $token = $req->input('token');
            $contrasena = $req->input('password');
            $confirmar_contrasena = $req->input('password_confirmation');

            if (! $token || ! $contrasena) {
                return response()->json([
                    'error' => 1,
                    'msg' => 'Token y nueva contraseña son requeridos',
                ], 400);
            }

            if ($contrasena !== $confirmar_contrasena) {
                return response()->json([
                    'error' => 1,
                    'msg' => 'Las contraseñas no coinciden',
                ], 400);
            }

            if (strlen($contrasena) < 8) {
                return response()->json([
                    'error' => 1,
                    'msg' => 'La contraseña debe tener al menos 8 caracteres',
                ], 400);
            }

            $result = $this->authService->resetPassword($token, $contrasena);

            $statusCode = $result['error'] === 0 ? 200 : 400;

            return response()->json($result, $statusCode);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 1,
                'msg' => Message::exception(),
            ], 500);
        }
    }
}
