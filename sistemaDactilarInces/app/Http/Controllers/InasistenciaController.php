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

            return response(json_encode($res), 200);
        } catch (\Exception $e) {
            return response(json_encode(['msg' => Message::exception(), 'error' => 1]), 500);
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

            $inasistenciaStored = $this->inasistenciaService->store($req->input());
            if (! $inasistenciaStored) {
                $error = 1;
                $msg = Message::duplicated();
            }
            $res = [
                'msg' => $msg,
                'error' => $error,
                'results' => $inasistenciaStored,
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
            $inasistenciaUpdated = $this->inasistenciaService->update($id, $req->input());
            if (! $inasistenciaUpdated) {
                $error = 1;
                $msg = Message::duplicated();
            }
            $res = [
                'error' => $error,
                'msg' => $msg,
                'results' => $inasistenciaUpdated,
            ];

            return response(json_encode($res), 200);
        } catch (\Exception $e) {
            return response(json_encode(['error' => 1, 'msg' => Message::exception()]), 500);
        }
    }

    public function delete(string $id)
    {
        try {
            $this->inasistenciaService->delete($id);

            return response(json_encode(['error' => 0, 'msg' => Message::deleted()]), 200);
        } catch (\Exception $e) {
            return response(json_encode(['error' => 1, 'msg' => Message::exception()]), 500);
        }
    }
}
