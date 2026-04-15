<?php

namespace App\Http\Controllers;

use App\Helpers\Message;
use App\Services\RolService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(protected RolService $rolService) {}

    public function index()
    {
        try {
            $rolesFound = $this->rolService->index();
            $res = [
                'msg' => '',
                'error' => 0,
                'results' => $rolesFound,
            ];

            return response(json_encode($res), 200);
        } catch (\Exception $e) {
            return response(json_encode(['msg' => Message::exception(), 'error' => 1]), 500);
        }
    }

    public function showById(string $id)
    {
        try {
            $rolFound = $this->rolService->showById($id);
            $res = [
                'msg' => '',
                'error' => 0,
                'results' => $rolFound,
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

            $rolStored = $this->rolService->store($req->input());
            if (! $rolStored) {
                $error = 1;
                $msg = Message::duplicated();
            }
            $res = [
                'msg' => $msg,
                'error' => $error,
                'results' => $rolStored,
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
            $rolUpdated = $this->rolService->update($id, $req->input());
            if (! $rolUpdated) {
                $error = 1;
                $msg = Message::duplicated();
            }
            $res = [
                'error' => $error,
                'msg' => $msg,
                'results' => $rolUpdated,
            ];

            return response(json_encode($res), 200);
        } catch (\Exception $e) {
            return response(json_encode(['error' => 1, 'msg' => Message::exception()]), 500);
        }
    }

    public function delete(string $id)
    {
        try {
            $this->rolService->delete($id);

            return response(json_encode(['error' => 0, 'msg' => Message::deleted()]), 200);
        } catch (\Exception $e) {
            return response(json_encode(['error' => 1, 'msg' => Message::exception()]), 500);
        }
    }
}
