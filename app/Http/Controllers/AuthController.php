<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

use App\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

class AuthController extends Controller
{

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['login', 'register']]);
    }

     /**
     * Store a secret message for the user. AES-256-CBC
     *
     * @param  $email
     * @return secret value
     */
    public function encrypt($email)
    {
        $user = User::where('email', $email)->first();
        $user->update([
            'secret' => "", // clean avoid maximum response
        ]);

        $encryptedValue = Crypt::encrypt($user);
        return $encryptedValue;
    }

     /**
     * Decrypt a secret message for the user. AES-256-CBC
     *
     * @param  $secret
     * @return decrypted value
     */
    public function decrypt($secret)
    {
        $decrypted="";

        try {
            $decrypted = Crypt::decrypt($secret);
        } catch (DecryptException $e) {
            return $e;
        }

        return $decrypted;
    }

    public function register(Request $request)
    {
       //validate incoming request
        $this->validate($request, [
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);

        $role_user = $request->input('role_user');

        $name = $request->input('name');
        $email = $request->input('email');
        $password = Hash::make($request->input('password'));
        $user_photo = $request->input('user_photo');

        try {
            // elquent
            $register = User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'user_photo' => $user_photo
            ]);

            // assign role access { role access must register in database : mysql }
            if (!$role_user)
                $role_user = "moderator";

            User::find($register->id)->assignRole($role_user);
            User::find($register->id)->assignPermission("view menu institution");
            User::find($register->id)->assignPermission("view menu organization");

            return $this->login($request);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Register Fail!',
                'throwable' => $e
            ],400);
        }
    }


    public function login(Request $request)
    {
        try {

            $credentials = $request->only(['email', 'password']);

            $user = User::where('email', $request->email)->first();

            // check user
            if (!$user)
                return $this->responseWithError(
                    "Email tidak terdaftar",
                    "",
                    400
                );

            // try auth with the user
            if (! $token = auth('api')->attempt($credentials))
                return $this->responseWithError(
                    "Password salah",
                    "",
                    400
                );

            // encrypt user data with AES-256-CBC
            $secret = $this->encrypt("$request->email");

            // update token and secret user data
            $user->update([
                'api_token' => $token,
                'secret' => $secret
            ]);

            // obj result data for json result
            $this->guard()->user()->myRole(); // for replace next data
            $this->guard()->user()->myPermission(); // for replace next data

            $dataUser = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $this->guard()->user()->myRole(),
                'permissions' => $this->guard()->user()->myPermission(),
                'avatar' => '',
                'api_token' => $token,
                'secret' => $secret
            ];

            // $data = [
            //         'user' => $dataUser,
            // ];

            return $this->responseWithSuccess(
                "Authentication Success! and Direct Load User",
                $dataUser,
                201
            );

        } catch (\Throwable $th) {
            return $this->responseWithError(
                    "error authentication , $th",
                    "",
                    400
            );
        }
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    public function update(Request $request)
    {
        $id = $request->input('id');
        $user = User::where('id', $id)->first();

        $name = $request->input('name');
        $email = $request->input('email');
        $user_photo = $request->input('user_photo');

        $password = Hash::make($request->input('password'));

        $state_update = $request->input('state_update');
        /**
        * 0 update password - state_update
        * 1 no update password - state_update
        * 2 update profile photo only
        */
        if($state_update == 0){ // 0 update password - state_update
            // elquent
            $user->update([
                        'name'      => $name,
                        'email'     => $email,
                        'password'  => $password
                    ]);
        }
        if($state_update == 1) { // 1 no update password - state_update
            // elquent
            $user->update([
                        'name' => $name,
                        'email' => $email
                    ]);
        }
        if($state_update == 2){ // 2 update profile photo only
            // elquent
            $user->update([
                        'user_photo' => $user_photo
            ]);
        }

        if($user)
           {
            $apiToken = base64_encode(Str::random(40));

            $user->update([
                'api_token' => $apiToken
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Update Success',
                'data' => [
                    'user' => $user,
                    'api_token' => $apiToken
                ]
            ],201);
        }
        else {
            return response()->json([
                    'success' => false,
                    'message' => '',
                    'data' => [
                        'user' => ''
                    ]
                ], 400);
        }
    }

    public function delete(Request $request)
    {
        $id = $request->input('id');
        # code...
        // elquent
        $delete = User::where('id', $id)
            ->delete();

        if ($delete) {
            return response()->json([
                'success' => true,
                'message' => 'delete user successfully'
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'update user unsuccessfully'
            ], 400);
        }
    }

}
