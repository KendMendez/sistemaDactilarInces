<?php

namespace App\Http\Controllers;

use App\Helpers\Message;
use App\Services\KioskoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KioskoController extends Controller
{
    public function __construct(protected KioskoService $kioskoService) {}

    public function templates(): JsonResponse
    {
        try {
            $templates = $this->kioskoService->getTemplates();

            return response()->json([
                'error' => 0,
                'msg' => '',
                'results' => $templates,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 1, 'msg' => Message::exception()], 500);
        }
    }

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
            return response()->json(['error' => 1, 'msg' => Message::exception()], 500);
        }
    }
}
