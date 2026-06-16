<?php

namespace App\Http\Controllers;

use App\Helpers\Message;
use App\Models\Empleado;
use App\Models\Privilegio;
use App\Services\EmpleadoAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class Authentication extends Controller
{
    public function __construct(protected EmpleadoAuthService $authService) {}

    public function login(Request $req): JsonResponse
    {
        try {
            $correo = $req->input('correo');
            $contrasena = $req->input('contraseña');

            Log::debug('[Login] Request received', [
                'correo' => $correo,
                'contrasena_present' => !is_null($contrasena),
                'contrasena_length' => is_string($contrasena) ? strlen($contrasena) : null,
                'content_type' => $req->header('Content-Type'),
                'method' => $req->method(),
            ]);

            $authenticated = $this->authService->login([
                'correo' => $correo,
                'contraseña' => $contrasena,
            ]);

            Log::debug('[Login] Service result', [
                'authenticated' => $authenticated ? 'truthy' : 'falsy',
                'result_keys' => $authenticated ? array_keys($authenticated) : [],
            ]);

            if (! $authenticated) {
                $this->authService->registerAttempt($correo, $req, false);

                return response()->json([
                    'error' => 1,
                    'msg' => 'Correo o contraseña incorrectos',
                ], 401);
            }

            $this->authService->registerAttempt($correo, $req, true, $authenticated['empleado'] ?? null);

            $user = $authenticated['empleado'] ?? null;
            if ($user && isset($user['id'])) {
                $userModel = Empleado::find($user['id']);
                if ($userModel) {
                    $userModel->load('roles.privilegios');
                    $empleadoRoles = $userModel->roles->pluck('role')->toArray();
                    if (in_array('Administrador', $empleadoRoles)) {
                        $privilegios = Privilegio::pluck('privilegio')->values();
                        $campos = Privilegio::pluck('campo')->unique()->values();
                    } else {
                        $privilegios = $userModel->roles->flatMap->privilegios->pluck('privilegio')->unique()->values();
                        $campos = $userModel->roles->flatMap->privilegios->pluck('campo')->unique()->values();
                    }
                }
            }

            $cookie = $this->authService->createSessionCookie($authenticated['token']);

            return response()->json([
                'error' => 0,
                'msg' => 'Inicio de sesión exitoso',
                'results' => [
                    'empleado' => $authenticated['empleado'] ?? null,
                    'token' => $authenticated['token'] ?? null,
                    'privilegios' => $privilegios ?? [],
                    'campos' => $campos ?? [],
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

    public function me(): JsonResponse
    {
        try {
            /** @var Empleado $user */
            $user = request()->user();
            if (! $user) {
                return response()->json(['error' => 1, 'msg' => 'No autenticado'], 401);
            }

            $user->load('roles.privilegios');

            $empleadoRoles = $user->roles->pluck('role')->toArray();

            if (in_array('Administrador', $empleadoRoles)) {
                $privilegios = Privilegio::pluck('privilegio')->values();
                $campos = Privilegio::pluck('campo')->unique()->values();
            } else {
                $privilegios = $user->roles->flatMap->privilegios->pluck('privilegio')->unique()->values();
                $campos = $user->roles->flatMap->privilegios->pluck('campo')->unique()->values();
            }

            return response()->json([
                'error' => 0,
                'empleado' => [
                    'id' => Crypt::encrypt($user->id),
                    'nombre' => $user->nombre,
                    'apellido' => $user->apellido,
                    'correo' => $user->correo,
                ],
                'roles' => $user->roles->pluck('role'),
                'privilegios' => $privilegios,
                'campos' => $campos,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 1,
                'msg' => Message::exception(),
            ], 500);
        }
    }

    public function logout(): JsonResponse
    {
        try {
            $cookie = $this->authService->deleteSessionCookie();

            return response()->json([
                'error' => 0,
                'msg' => 'Sesión cerrada correctamente',
            ], 200)->withCookie($cookie);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 1,
                'msg' => Message::exception(),
            ], 500);
        }
    }
}
