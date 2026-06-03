<?php

namespace App\Http\Controllers;

use App\Helpers\Message;
use App\Services\KioskoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KioskoController extends Controller
{
    public function __construct(protected KioskoService $kioskoService) {}

    public function verificar(Request $req): JsonResponse
    {
        try {
            $validated = $req->validate([
                'id_empleado' => 'required|string',
            ]);

            $result = $this->kioskoService->verificar($validated['id_empleado']);

            $statusCode = $result['error'] ? 200 : 200;

            return response()->json($result, $statusCode);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[Kiosko] verificar exception', ['msg' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
            return response()->json(['error' => 1, 'msg' => Message::exception()], 500);
        }
    }

    public function match(Request $req): JsonResponse
    {
        try {
            $validated = $req->validate([
                'huella' => 'required|string',
            ]);

            $result = $this->kioskoService->matchFingerprint($validated['huella']);

            if ($result) {
                return response()->json([
                    'match' => true,
                    'id_empleado' => $result['id_empleado'],
                    'nombre' => $result['nombre'],
                    'score' => $result['score'],
                ], 200);
            }

            return response()->json(['match' => false], 200);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[Kiosko] match exception', ['msg' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
            return response()->json(['match' => false, 'error' => 1, 'msg' => Message::exception()], 500);
        }
    }
}
