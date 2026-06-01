<?php

namespace App\Http\Controllers;

use App\Helpers\Message;
use App\Services\CargoService;
use Illuminate\Http\Request;

class CargoController extends Controller
{
    public function __construct(protected CargoService $cargoService) {}

    public function index()
    {
        try {
            $cargosFound = $this->cargoService->index();
            $res = [
                'msg' => '',
                'error' => 0,
                'results' => $cargosFound,
            ];

            return response()->json($res, 200);
        } catch (\Exception $e) {
            return response()->json(['msg' => Message::exception(), 'error' => 1], 500);
        }
    }

    public function showById(string $id)
    {
        try {
            $cargoFound = $this->cargoService->showById($id);
            $res = [
                'msg' => '',
                'error' => 0,
                'results' => $cargoFound,
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
                'cargo' => 'required|string|max:255',
            ]);

            $error = 0;
            $msg = Message::stored();

            $cargoStored = $this->cargoService->store($validated);
            if (! $cargoStored) {
                $error = 1;
                $msg = Message::duplicated();
            }
            $res = [
                'msg' => $msg,
                'error' => $error,
                'results' => $cargoStored,
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
                'cargo' => 'required|string|max:255',
            ]);

            $error = 0;
            $msg = Message::updated();
            $cargoUpdated = $this->cargoService->update($id, $validated);
            if (! $cargoUpdated) {
                $error = 1;
                $msg = Message::duplicated();
            }
            $res = [
                'error' => $error,
                'msg' => $msg,
                'results' => $cargoUpdated,
            ];

            return response()->json($res, 200);
        } catch (\Exception $e) {

            return response()->json(['error' => 1, 'msg' => Message::exception()], 500);
        }
    }

    public function delete(Request $req, string $id)
    {
        try {
            $result = $this->cargoService->delete($id, $req->boolean('force'));

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
