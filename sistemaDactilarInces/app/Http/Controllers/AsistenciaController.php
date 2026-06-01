<?php

namespace App\Http\Controllers;

use App\Helpers\Message;
use App\Services\AsistenciaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AsistenciaController extends Controller
{
    public function __construct(protected AsistenciaService $asistenciaService) {}

    public function index(): JsonResponse
    {
        try {
            $asistenciasFound = $this->asistenciaService->index();
            $res = [
                'msg' => '',
                'error' => 0,
                'results' => $asistenciasFound,
            ];

            return response()->json($res, 200);
        } catch (\Exception $e) {
            return response()->json(['msg' => Message::exception(), 'error' => 1], 500);
        }
    }

    public function showById(string $id): JsonResponse
    {
        try {
            $asistenciaFound = $this->asistenciaService->showById($id);
            $res = [
                'msg' => '',
                'error' => 0,
                'results' => $asistenciaFound,
            ];

            return response()->json($res, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 1, 'msg' => Message::exception()], 500);
        }
    }

    public function store(Request $req): JsonResponse
    {
        try {
            $validated = $req->validate([
                'id_empleado' => 'required|string',
                'fecha' => 'required|date',
                'hora_entrada' => 'required|string',
                'hora_salida' => 'required|string',
            ]);

            $error = 0;
            $msg = Message::stored();

            $asistenciaStored = $this->asistenciaService->store($validated);
            if (! $asistenciaStored) {
                $error = 1;
                $msg = Message::duplicated();
            }
            $res = [
                'msg' => $msg,
                'error' => $error,
                'results' => $asistenciaStored,
            ];

            return response()->json($res, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 1, 'msg' => Message::exception()], 500);
        }
    }

    public function update(Request $req, string $id): JsonResponse
    {
        try {
            $validated = $req->validate([
                'id_empleado' => 'required|string',
                'fecha' => 'required|date',
                'hora_entrada' => 'required|string',
                'hora_salida' => 'required|string',
            ]);

            $error = 0;
            $msg = Message::updated();
            $asistenciaUpdated = $this->asistenciaService->update($id, $validated);
            if (! $asistenciaUpdated) {
                $error = 1;
                $msg = Message::duplicated();
            }
            $res = [
                'error' => $error,
                'msg' => $msg,
                'results' => $asistenciaUpdated,
            ];

            return response()->json($res, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 1, 'msg' => Message::exception()], 500);
        }
    }

    public function delete(string $id): JsonResponse
    {
        try {
            $this->asistenciaService->delete($id);

            return response()->json(['error' => 0, 'msg' => Message::deleted()], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 1, 'msg' => Message::exception()], 500);
        }
    }

    public function pending(): JsonResponse
    {
        try {
            $pendientes = $this->asistenciaService->pending();

            return response()->json([
                'error' => 0,
                'msg' => '',
                'results' => $pendientes,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 1, 'msg' => Message::exception()], 500);
        }
    }

    public function approve(string $id): JsonResponse
    {
        try {
            $this->asistenciaService->approve($id);

            return response()->json([
                'error' => 0,
                'msg' => 'Asistencia aprobada correctamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 1, 'msg' => Message::exception()], 500);
        }
    }

    public function reject(string $id): JsonResponse
    {
        try {
            $this->asistenciaService->reject($id);

            return response()->json([
                'error' => 0,
                'msg' => 'Asistencia rechazada.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 1, 'msg' => Message::exception()], 500);
        }
    }
}
