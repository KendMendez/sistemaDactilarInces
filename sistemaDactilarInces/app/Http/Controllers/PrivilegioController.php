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

            return response()->json($res, 200);
        } catch (\Exception $e) {
            return response()->json(['msg' => Message::exception(), 'error' => 1], 500);
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

            return response()->json($res, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 1, 'msg' => Message::exception()], 500);
        }
    }

    public function store(Request $req)
    {
        try {
            $validated = $req->validate([
                'privilegio' => 'required|string|max:255',
            ]);

            $error = 0;
            $msg = Message::stored();

            $privilegioStored = $this->privilegioService->store($validated);
            if (! $privilegioStored) {
                $error = 1;
                $msg = Message::duplicated();
            }
            $res = [
                'msg' => $msg,
                'error' => $error,
                'results' => $privilegioStored,
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
                'privilegio' => 'required|string|max:255',
            ]);

            $error = 0;
            $msg = Message::updated();
            $privilegioUpdated = $this->privilegioService->update($id, $validated);
            if (! $privilegioUpdated) {
                $error = 1;
                $msg = Message::duplicated();
            }
            $res = [
                'error' => $error,
                'msg' => $msg,
                'results' => $privilegioUpdated,
            ];

            return response()->json($res, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 1, 'msg' => Message::exception()], 500);
        }
    }

    public function delete(Request $req, string $id)
    {
        try {
            $result = $this->privilegioService->delete($id, $req->boolean('force'));

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
