<?php

namespace App\Http\Controllers;

use App\Helpers\Message;
use App\Services\InasistenciaService;
use Illuminate\Http\Request;

class InasistenciaController extends Controller
{
    public function __construct(protected InasistenciaService $inasistenciaService) {}

    public function index()
    {
        try {
            $inasistenciasFound = $this->inasistenciaService->index();
            $res = [
                'msg' => '',
                'error' => 0,
                'results' => $inasistenciasFound,
            ];

            return response()->json($res, 200);
        } catch (\Exception $e) {
            return response()->json(['msg' => Message::exception(), 'error' => 1], 500);
        }
    }

    public function showById(string $id)
    {
        try {
            $inasistenciaFound = $this->inasistenciaService->showById($id);
            $res = [
                'msg' => '',
                'error' => 0,
                'results' => $inasistenciaFound,
            ];

            return response()->json($res, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 1, 'msg' => Message::exception()], 500);
        }
    }

    public function store(Request $req)
    {
        try {
            $validated = $req->validate([
                'id_empleado' => 'required|string',
                'fecha' => 'required|date',
                'justificacion' => 'required|string',
            ]);

            $error = 0;
            $msg = Message::stored();

            $inasistenciaStored = $this->inasistenciaService->store($validated);
            if (! $inasistenciaStored) {
                $error = 1;
                $msg = Message::duplicated();
            }
            $res = [
                'msg' => $msg,
                'error' => $error,
                'results' => $inasistenciaStored,
            ];

            return response()->json($res, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 1, 'msg' => Message::exception()], 500);
        }
    }

    public function update(Request $req, string $id)
    {
        try {
            $validated = $req->validate([
                'id_empleado' => 'sometimes|string',
                'fecha' => 'sometimes|date',
                'justificacion' => 'sometimes|string',
            ]);

            $error = 0;
            $msg = Message::updated();
            $inasistenciaUpdated = $this->inasistenciaService->update($id, $validated);
            if (! $inasistenciaUpdated) {
                $error = 1;
                $msg = Message::duplicated();
            }
            $res = [
                'error' => $error,
                'msg' => $msg,
                'results' => $inasistenciaUpdated,
            ];

            return response()->json($res, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 1, 'msg' => Message::exception()], 500);
        }
    }

    public function delete(string $id)
    {
        try {
            $this->inasistenciaService->delete($id);

            return response()->json(['error' => 0, 'msg' => Message::deleted()], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 1, 'msg' => Message::exception()], 500);
        }
    }
}
