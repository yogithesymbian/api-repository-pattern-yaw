<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{

    public function guard()
    {
        return \Auth::Guard('api');
    }
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
     protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'user' => $this->guard()->user(),
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    protected function responseWithSuccess($msg, $data, $code)
    {
        return response()->json([
            "success" => true,
            "message" => $msg,
            "data" => $data
        ], $code);
    }

    protected function responseWithError($msg, $data, $code)
    {
        return response()->json([
            "success" => false,
            "message" => $msg,
            "data" => $data
        ], $code);
    }
}
