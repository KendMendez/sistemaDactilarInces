<?php

namespace App\Http\Controllers;

use App\Helpers\Message;
use App\Services\EmpleadoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class EmpleadoController extends Controller
{
    public function __construct(protected EmpleadoService $empleadoService) {}

    public function index()
    {
        try {
            $empleadosFound = $this->empleadoService->index();
            $res = [
                'msg' => '',
                'error' => 0,
                'results' => $empleadosFound,
            ];

            return response()->json($res, 200);
        } catch (\Exception $e) {
            return response()->json(['msg' => Message::exception(), 'error' => 1], 500);
        }
    }

    public function showById(string $id)
    {
        try {
            $empleadoFound = $this->empleadoService->showById($id);
            $res = [
                'msg' => '',
                'error' => 0,
                'results' => $empleadoFound,
            ];

            return response()->json($res, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 1, 'msg' => Message::exception()], 500);
        }
    }

    public function store(Request $req)
    {
        Log::debug('[Empleado] store request received', [
            'keys' => array_keys($req->all()),
            'has_contraseña' => $req->has('contraseña'),
            'has_id_cargo' => $req->has('id_cargo'),
            'content_type' => $req->header('Content-Type'),
        ]);
        try {
            $validated = $req->validate([
                'id_cargo' => 'required|string',
                'nombre' => 'required|string|max:255',
                'apellido' => 'required|string|max:255',
                'telefono' => 'required|string|max:255',
                'identificacion' => 'required|string|max:255',
                'correo' => 'required|email|max:255',
                'contraseña' => 'required|string|min:6',
                'sexo' => 'required|string|max:255',
                'foto' => 'nullable|string',
                'huella_pulgar' => 'nullable|string',
                'huella_indice' => 'nullable|string',
            ]);

            $error = 0;
            $msg = Message::stored();

            $empleadoStored = $this->empleadoService->store($validated);
            if (! $empleadoStored) {
                $error = 1;
                $msg = Message::duplicated();
                Log::warning('[Empleado] store duplicated', ['validated_keys' => array_keys($validated)]);
            }
            $res = [
                'msg' => $msg,
                'error' => $error,
                'results' => $empleadoStored,
            ];

            return response()->json($res, 201);
        } catch (ValidationException $e) {
            Log::warning('[Empleado] store validation failed', ['errors' => $e->errors()]);
            return response()->json(['error' => 1, 'msg' => 'Datos inválidos', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('[Empleado] store exception', [
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json(['error' => 1, 'msg' => Message::exception()], 500);
        }
    }

    public function update(Request $req, string $id)
    {
        try {
            $validated = $req->validate([
                'id_cargo' => 'sometimes|string',
                'nombre' => 'sometimes|string|max:255',
                'apellido' => 'sometimes|string|max:255',
                'telefono' => 'sometimes|string|max:255',
                'identificacion' => 'sometimes|string|max:255',
                'correo' => 'sometimes|email|max:255',
                'contraseña' => 'sometimes|string|min:6',
                'sexo' => 'sometimes|string|max:255',
                'foto' => 'nullable|string',
                'huella_pulgar' => 'nullable|string',
                'huella_indice' => 'nullable|string',
            ]);

            $error = 0;
            $msg = Message::updated();
            $empleadoUpdated = $this->empleadoService->update($id, $validated);
            if (! $empleadoUpdated) {
                $error = 1;
                $msg = Message::duplicated();
            }
            $res = [
                'error' => $error,
                'msg' => $msg,
                'results' => $empleadoUpdated,
            ];

            return response()->json($res, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 1, 'msg' => Message::exception()], 500);
        }
    }

    public function delete(Request $req, string $id)
    {
        try {
            $result = $this->empleadoService->delete($id, $req->boolean('force'));

            if (is_array($result)) {
                return response()->json([
                    'error' => 0,
                    'requires_confirmation' => true,
                    'msg' => $result['msg'],
                    'dependencies' => $result['dependencies'],
                ], 409);
            }

            return response()->json(['error' => 0, 'msg' => Message::deleted()], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 1, 'msg' => Message::exception()], 500);
        }
    }
}
