<?php

namespace App\Http\Controllers;

use App\Helpers\Message;
use App\Services\HorarioService;
use Illuminate\Http\Request;

class HorarioController extends Controller
{
    public function __construct(protected HorarioService $horarioService) {}

    public function index()
    {
        try {
            $horariosFound = $this->horarioService->index();
            $res = [
                'msg' => '',
                'error' => 0,
                'results' => $horariosFound,
            ];

            return response()->json($res, 200);
        } catch (\Exception $e) {
            return response()->json(['msg' => Message::exception(), 'error' => 1], 500);
        }
    }
    public function store(Request $req)
    {
        try {
            $validated = $req->validate([
                'id_empleado' => 'required|string',
                'dia' => 'required|array',
                'dia.*' => 'required|string|distinct',
                'hora_entrada' => 'nullable|string',
                'hora_salida' => 'nullable|string',
                'hora_entrada_tolerada' => 'required|string',
                'hora_salida_tolerada' => 'required|string',
            ]);

            $error = 0;
            $msg = Message::stored();

            $horarioStored = $this->horarioService->store($validated);
            if (! $horarioStored) {
                $error = 1;
                $msg = Message::duplicated();
            }
            $res = [
                'msg' => $msg,
                'error' => $error,
                'results' => $horarioStored,
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
                'id_empleado' => 'required|string',
                'dia' => 'required|array',
                'dia.*' => 'required|string|distinct',
                'hora_entrada' => 'nullable|string',
                'hora_salida' => 'nullable|string',
                'hora_entrada_tolerada' => 'required|string',
                'hora_salida_tolerada' => 'required|string',
            ]);

            $error = 0;
            $msg = Message::updated();
            $horarioUpdated = $this->horarioService->update($id, $validated);
            if (! $horarioUpdated) {
                $error = 1;
                $msg = Message::duplicated();
            }
            $res = [
                'error' => $error,
                'msg' => $msg,
                'results' => $horarioUpdated,
            ];

            return response()->json($res, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 1, 'msg' => Message::exception()], 500);
        }
    }

    public function delete(string $id)
    {
        try {
            $this->horarioService->delete($id);

            return response()->json(['error' => 0, 'msg' => Message::deleted()], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 1, 'msg' => Message::exception()], 500);
        }
    }
}
