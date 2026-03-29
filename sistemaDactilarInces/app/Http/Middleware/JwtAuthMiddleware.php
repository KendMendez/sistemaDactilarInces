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
        $token = $request->cookie('token');

        if (! $token) {
            return response()->json([
                'error' => 1,
                'msg' => 'No autenticado. Token no proporcionado.',
            ], 401);
        }

        try {
            $key = env('JWT_SECRET');
            $decoded = JWT::decode($token, new Key($key, 'HS256'));

            $empleado = Empleado::find($decoded->user);

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
                'msg' => 'Error al autenticar.',
            ], 401);
        }
    }
}
