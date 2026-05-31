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

            return response()->json($res, 200);
        } catch (\Exception $e) {
            return response()->json(['msg' => Message::exception(), 'error' => 1], 500);
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

            return response()->json($res, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 1, 'msg' => Message::exception()], 500);
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

            return response()->json($res, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 1, 'msg' => Message::exception()], 500);
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

            return response()->json($res, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 1, 'msg' => Message::exception()], 500);
        }
    }

    public function delete(Request $req, string $id)
    {
        try {
            $result = $this->rolService->delete($id, $req->boolean('force'));

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
