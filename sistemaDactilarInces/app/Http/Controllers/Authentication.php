<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Message;
use App\Services\EmpleadoAuthService;

class Authentication extends Controller
{
    public function __construct(protected EmpleadoAuthService $empleadoAuthService){}
    public function login(Request $req)
    {
        try{
            $msg = 'Inicio de sesión exitoso';
            $error = 0;
            $logged = $this->empleadoAuthService->login($req->input());

            if(!$logged){
                $error = 1;
                $msg = 'Email o contraseña incorrectos';
            }
            $res = [
                'error' => $error,
                'msg' => $msg,
                'results' => $logged
            ];
            return response()->json($res, '200');

        }catch (\Exception $e){ 
           return response()->json(['error' => 1, 'msg' => Message::exception()], '500');  
        }
    }
}
