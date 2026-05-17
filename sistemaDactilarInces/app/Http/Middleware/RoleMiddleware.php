<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $empleado = $request->attributes->get('empleado');

        if (! $empleado) {
            return response()->json([
                'error' => 1,
                'msg' => 'No autenticado.',
            ], 401);
        }

        $empleadoRoles = $empleado->roles()->pluck('role')->toArray();

        if (in_array('Administrador', $empleadoRoles)) {
            return $next($request);
        }

        $hasRole = false;
        foreach ($roles as $role) {
            if (in_array($role, $empleadoRoles)) {
                $hasRole = true;
                break;
            }
        }

        if (! $hasRole) {
            return response()->json([
                'error' => 1,
                'msg' => 'No tienes permisos para acceder a esta ruta.',
            ], 403);
        }

        return $next($request);
    }
}
