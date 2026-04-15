<?php

namespace App\Http\Controllers;

use App\Helpers\Message;
use App\Services\PrivilegioService;
use Illuminate\Http\Request;

class PrivilegioController extends Controller
{
    public function __construct(protected PrivilegioService $privilegioService) {}

    public function index()
    {
        try {
            $privilegiosFound = $this->privilegioService->index();
            $res = [
                'msg' => '',
                'error' => 0,
                'results' => $privilegiosFound,
            ];

            return response(json_encode($res), 200);
        } catch (\Exception $e) {
            return response(json_encode(['msg' => Message::exception(), 'error' => 1]), 500);
        }
    }

    public function showById(string $id)
    {
        try {
            $privilegioFound = $this->privilegioService->showById($id);
            $res = [
                'msg' => '',
                'error' => 0,
                'results' => $privilegioFound,
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

            $privilegioStored = $this->privilegioService->store($req->input());
            if (! $privilegioStored) {
                $error = 1;
                $msg = Message::duplicated();
            }
            $res = [
                'msg' => $msg,
                'error' => $error,
                'results' => $privilegioStored,
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
            $privilegioUpdated = $this->privilegioService->update($id, $req->input());
            if (! $privilegioUpdated) {
                $error = 1;
                $msg = Message::duplicated();
            }
            $res = [
                'error' => $error,
                'msg' => $msg,
                'results' => $privilegioUpdated,
            ];

            return response(json_encode($res), 200);
        } catch (\Exception $e) {
            return response(json_encode(['error' => 1, 'msg' => Message::exception()]), 500);
        }
    }

    public function delete(string $id)
    {
        try {
            $this->privilegioService->delete($id);

            return response(json_encode(['error' => 0, 'msg' => Message::deleted()]), 200);
        } catch (\Exception $e) {
            return response(json_encode(['error' => 1, 'msg' => Message::exception()]), 500);
        }
    }
}
