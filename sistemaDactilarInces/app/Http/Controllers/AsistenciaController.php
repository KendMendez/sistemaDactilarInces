<?php

namespace App\Http\Controllers;

use App\Helpers\Message;
use App\Services\AsistenciaService;
use Illuminate\Http\Request;

class AsistenciaController extends Controller
{
    public function __construct(protected AsistenciaService $asistenciaService) {}

    public function index()
    {
        try {
            $asistenciasFound = $this->asistenciaService->index();
            $res = [
                'msg' => '',
                'error' => 0,
                'results' => $asistenciasFound,
            ];

            return response(json_encode($res), 200);
        } catch (\Exception $e) {
            return response(json_encode(['msg' => Message::exception(), 'error' => 1]), 500);
        }
    }

    public function showById(string $id)
    {
        try {
            $asistenciaFound = $this->asistenciaService->showById($id);
            $res = [
                'msg' => '',
                'error' => 0,
                'results' => $asistenciaFound,
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

            $asistenciaStored = $this->asistenciaService->store($req->input());
            if (! $asistenciaStored) {
                $error = 1;
                $msg = Message::duplicated();
            }
            $res = [
                'msg' => $msg,
                'error' => $error,
                'results' => $asistenciaStored,
            ];

            return response(json_encode($res), 201);
        } catch (\Exception $e) {
            return response(json_encode(['error' => 1, 'msg' => Message::exception()]), 500);
        }
    }

    public function update(Request $req, string $id)
    {
        try {
            $error = 0;
            $msg = Message::updated();
            $asistenciaUpdated = $this->asistenciaService->update($id, $req->input());
            if (! $asistenciaUpdated) {
                $error = 1;
                $msg = Message::duplicated();
            }
            $res = [
                'error' => $error,
                'msg' => $msg,
                'results' => $asistenciaUpdated,
            ];

            return response(json_encode($res), 200);
        } catch (\Exception $e) {
            return response(json_encode(['error' => 1, 'msg' => Message::exception()]), 500);
        }
    }

    public function delete(string $id)
    {
        try {
            $this->asistenciaService->delete($id);

            return response(json_encode(['error' => 0, 'msg' => Message::deleted()]), 200);
        } catch (\Exception $e) {
            return response(json_encode(['error' => 1, 'msg' => Message::exception()]), 500);
        }
    }
}
