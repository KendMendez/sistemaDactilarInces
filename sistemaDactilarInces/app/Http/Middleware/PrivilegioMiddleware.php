<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PrivilegioMiddleware
{
    public function handle(Request $request, Closure $next, ...$privilegios): Response
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

        $empleadoPrivilegios = $empleado->roles()
            ->with('privilegios')
            ->get()
            ->pluck('privilegios.*.privilegio')
            ->flatten()
            ->unique()
            ->toArray();

        $hasPrivilegio = false;
        foreach ($privilegios as $privilegio) {
            if (in_array($privilegio, $empleadoPrivilegios)) {
                $hasPrivilegio = true;
                break;
            }
        }

        if (! $hasPrivilegio) {
            return response()->json([
                'error' => 1,
                'msg' => 'No tienes permisos para acceder a esta ruta.',
            ], 403);
        }

        return $next($request);
    }
}
