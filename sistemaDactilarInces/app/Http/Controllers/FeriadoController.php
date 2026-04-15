<?php

namespace App\Http\Controllers;

use App\Helpers\Message;
use App\Services\FeriadoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeriadoController extends Controller
{
    public function __construct(protected FeriadoService $feriadoService) {}

    public function index()
    {
        try {
            $feriadosFound = $this->feriadoService->index();
            $res = [
                'msg' => '',
                'error' => 0,
                'results' => $feriadosFound,
            ];

            return response(json_encode($res), 200);
        } catch (\Exception $e) {
            return response(json_encode(['msg' => Message::exception(), 'error' => 1]), 500);
        }
    }

    public function showById(string $id)
    {
        try {
            $feriadoFound = $this->feriadoService->showById($id);
            $res = [
                'msg' => '',
                'error' => 0,
                'results' => $feriadoFound,
            ];

            return response(json_encode($res), 200);
        } catch (\Exception $e) {
            return response(json_encode(['error' => 1, 'msg' => Message::exception()]), 500);
        }
    }

    public function store(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'fecha' => 'required|string',
                'descripcion' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => 1, 'msg' => $validator->errors()->first()], 400);
            }

            $error = 0;
            $msg = Message::stored();

            $feriadoStored = $this->feriadoService->store($validator->validated());
            if (! $feriadoStored) {
                $error = 1;
                $msg = Message::duplicated();
            }
            $res = [
                'msg' => $msg,
                'error' => $error,
                'results' => $feriadoStored,
            ];

            return response(json_encode($res), 201);
        } catch (\Exception $e) {
            dd($e);

            return response(json_encode(['error' => 1, 'msg' => Message::exception()]), 500);
        }
    }

    public function update(Request $req, string $id)
    {
        try {
            $validator = Validator::make($req->all(), [
                'fecha' => 'required|string',
                'descripcion' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => 1, 'msg' => $validator->errors()->first()], 400);
            }

            $error = 0;
            $msg = Message::updated();
            $feriadoUpdated = $this->feriadoService->update($id, $validator->validated());
            if (! $feriadoUpdated) {
                $error = 1;
                $msg = Message::duplicated();
            }
            $res = [
                'error' => $error,
                'msg' => $msg,
                'results' => $feriadoUpdated,
            ];

            return response(json_encode($res), 200);
        } catch (\Exception $e) {
            return response(json_encode(['error' => 1, 'msg' => Message::exception()]), 500);
        }
    }

    public function delete(string $id)
    {
        try {
            $this->feriadoService->delete($id);

            return response(json_encode(['error' => 0, 'msg' => Message::deleted()]), 200);
        } catch (\Exception $e) {
            return response(json_encode(['error' => 1, 'msg' => Message::exception()]), 500);
        }
    }
}
