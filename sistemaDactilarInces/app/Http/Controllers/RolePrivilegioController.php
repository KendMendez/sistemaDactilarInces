<?php

namespace App\Http\Controllers;

use App\Helpers\Message;
use App\Services\RolePrivilegioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RolePrivilegioController extends Controller
{
    public function __construct(protected RolePrivilegioService $rolePrivilegioService) {}

    public function findPrivilegiosByRoleId(string $roleId): JsonResponse
    {
        try {
            $privilegiosFound = $this->rolePrivilegioService->findPrivilegiosByRoleId($roleId);
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

    public function store(Request $req): JsonResponse
    {
        try {
            $this->rolePrivilegioService->store($req->input());

            return response()->json([
                'msg' => Message::stored(),
                'error' => 0,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['msg' => Message::exception(), 'error' => 1], 500);
        }
    }
}
