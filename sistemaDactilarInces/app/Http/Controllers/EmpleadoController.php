<?php

namespace App\Http\Controllers;

use App\Helpers\Message;
use App\Services\EmpleadoService;
use Illuminate\Http\Request;

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

            return response(json_encode($res), 200);
        } catch (\Exception $e) {
            return response(json_encode(['msg' => Message::exception(), 'error' => 1]), 500);
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

            return response(json_encode($res), 200);
        } catch (\Exception $e) {
            return response(json_encode(['error' => 1, 'msg' => Message::exception()]), 500);
        }
    }

    public function store(Request $req)
    {
        try {
            $error = 0;
            $msg = Message::stored();

            $empleadoStored = $this->empleadoService->store($req->input());
            if (! $empleadoStored) {
                $error = 1;
                $msg = Message::duplicated();
            }
            $res = [
                'msg' => $msg,
                'error' => $error,
                'results' => $empleadoStored,
            ];

            return response(json_encode($res), 201);
        } catch (\Exception $e) {
            return response(json_encode(['error' => 1, 'msg' => $e->getMessage()]), 500);
        }
    }

    public function update(Request $req, string $id)
    {
        try {
            $error = 0;
            $msg = Message::updated();
            $empleadoUpdated = $this->empleadoService->update($id, $req->input());
            if (! $empleadoUpdated) {
                $error = 1;
                $msg = Message::duplicated();
            }
            $res = [
                'error' => $error,
                'msg' => $msg,
                'results' => $empleadoUpdated,
            ];

            return response(json_encode($res), 200);
        } catch (\Exception $e) {
            return response(json_encode(['error' => 1, 'msg' => $e->getMessage()]), 500);
        }
    }

    public function delete(string $id)
    {
        try {
            $this->empleadoService->delete($id);

            return response(json_encode(['error' => 0, 'msg' => Message::deleted()]), 200);
        } catch (\Exception $e) {
            return response(json_encode(['error' => 1, 'msg' => Message::exception()]), 500);
        }
    }
}
