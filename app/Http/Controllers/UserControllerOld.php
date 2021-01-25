<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\Mail\ResetPasswordMail;
use App\User;
use Illuminate\Support\Str;

class UserController extends Controller
{
     /**
     * Store a secret message for the user. AES-256-CBC
     *
     * @param  Request  $request
     * @return Response
     */
    public function encrypt(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        $user->update([
            'secret' => "", // clean avoid maximum response
        ]);

        $encryptedValue = Crypt::encrypt($user);

        $user->update([
            'secret' => $encryptedValue,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Store Secret',
            'secret' => $user->secret,
            'token' => $user->api_token,
        ], 200);
    }

     /**
     * Decrypt a secret message for the user. AES-256-CBC
     *
     * @param  Request  $request
     * @return Response
     */
    public function decrypt(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        $decrypted="";

        try {
            $decrypted = Crypt::decrypt($user->secret);
        } catch (DecryptException $e) {
            return response()->json([
                'status' => false,
                'message' => $e
            ], 200);
        }

        return response()->json([
            'status' => true,
            'message' => 'decrypted Secret Value',
            'decrypted' => $decrypted,
            'token' => $user->api_token,
        ], 200);
    }

    public function setRedis(Request $request)
    {
        $this->validate($request, [
            'key' => 'required'
        ]);

        $key = $request->key;
        $seconds = 86400;

        app('redis')->set($key, "token123 sudah");

        // if(app('redis')->exists($key)){

        //     app('redis')->set($key, "token123 sudah");

        // } else {

        //     app('redis')->set($key, "token123 belum");

        // }

        app('redis')->expire($key, $seconds);

        return response()->json([
            'status' => true,
            'message' => 'decrypted Secret Value',
            'redis' => app('redis')->get($key),
        ], 200);
    }

    public function sendResetToken(Request $request)
    {
        //VALIDASI EMAIL UNTUK MEMASTIKAN BAHWA EMAILNYA SUDAH ADA
        $this->validate($request, [
            'email' => 'required|email|exists:users'
        ]);

        $apiToken = "".rand(1000,9999)."";
        // $apiToken = base64_encode(str_random(40));

        //GET DATA USER BERDASARKAN EMAIL TERSEBUT
        $user = User::where('email', $request->email)->first();
        //LALU GENERATE TOKENNYA
        $user->update([
            'remember_token' => $apiToken,
        ]);

        //kirim token via email sebagai otentikasi kepemilikan
        Mail::to($user->email)->send(new ResetPasswordMail($user));

        return response()->json([
            'status' => true,
            'message' => 'token telah dikirim',
            'token' => $user->remember_token
        ], 200);
    }

    public function verifyResetPassword(Request $request)
    {
        $rememberToken = $request->input('remember_token');
        //VALIDASI EMAIL UNTUK MEMASTIKAN BAHWA EMAILNYA SUDAH ADA
        $this->validate($request, [
            'email' => 'required|email|exists:users',
            'password' => 'required|string|min:6'
        ]);

        //GET DATA USER BERDASARKAN EMAIL TERSEBUT
        $user = User::where('email', $request->email)->first();

        if ($user->remember_token == $rememberToken) {
            # code...
            //UPDATE PASSWORD USER TERKAIT
            $user->update([
                'password' => Hash::make($request->password),
                'remember_token' => ""
                ]
            );
            if($user){
                return response()->json([
                    'status' => true,
                    'message' => 'change password success',
                    'data' =>  [
                            'user' => $user
                        ]
                ], 200);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'token salah',
                'token' => $rememberToken,
                'data' =>  [
                        'user' => ""
                    ]
            ], 400);
        }

    }
}
