<?php

namespace App\Http\Middleware;

use App\Models\Empleado;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie('token') ?? $request->header('Authorization');

        if (str_starts_with($token, 'Bearer ')) {
            $token = substr($token, 7);
        }

        if (! $token) {
            $cookieHeader = $request->header('Cookie');
            if ($cookieHeader) {
                if (preg_match('/token=([^;]+)/', $cookieHeader, $matches)) {
                    $token = $matches[1];
                }
            }
        }

        if (! $token) {
            return response()->json([
                'error' => 1,
                'msg' => 'Acceso denegado. Debe iniciar sesión.',
                'debug' => [
                    'has_cookie_header' => (bool) $request->header('Cookie'),
                    'cookie_header' => $request->header('Cookie'),
                ],
            ], 401);
        }

        try {
            $key = env('JWT_SECRET');
            $decoded = JWT::decode($token, new Key($key, 'HS256'));

            $empleado = Empleado::with('roles')->find($decoded->user);

            if (! $empleado) {
                return response()->json([
                    'error' => 1,
                    'msg' => 'Usuario no encontrado.',
                ], 401);
            }

            $request->attributes->set('empleado', $empleado);
            $request->setUserResolver(function () use ($empleado) {
                return $empleado;
            });

            return $next($request);

        } catch (\Firebase\JWT\ExpiredException $e) {
            return response()->json([
                'error' => 1,
                'msg' => 'Token expirado.',
            ], 401);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return response()->json([
                'error' => 1,
                'msg' => 'Token inválido.',
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 1,
                'msg' => 'Error al autenticar: '.$e->getMessage(),
            ], 401);
        }
    }
}
