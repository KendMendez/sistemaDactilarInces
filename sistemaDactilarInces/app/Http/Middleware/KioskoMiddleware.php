<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KioskoMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-Kiosko-Key');

        if (! $key || $key !== config('kiosko.api_key')) {
            return response()->json([
                'error' => 1,
                'msg' => 'Acceso denegado.',
            ], 401);
        }

        return $next($request);
    }
}
