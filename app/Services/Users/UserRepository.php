<?php

namespace App\Services\Users;

use App\Exceptions\InvalidCredentialException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App\Services\Users\Models\User; // relation roles and permissions
use App\Http\Controllers\Controller;

class UserRepository extends Controller
{
    // public function index()
    // {
    //     return User::paginate(5);
    // }

    // public function getById($id)
    // {
    //     return User::find($id);
    // }

    public function register(array $data)
    {
        try {

            $user = array_merge($data, [
                'password' => Hash::make($data['password']),
            ]);

            $register = User::create($user);
            return $this->login($data);

            // $role_user = Arr::get($data, 'role_user');
            // // // assign role access { role access must register in database : mysql }
            // if (!$role_user)
            //     $role_user = "moderator";

            // User::find($register->id)->assignRole("admin");
            // User::find($register->id)->assignPermission("view menu institution");
            // User::find($register->id)->assignPermission("view menu organization");


        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Register Fail!',
                'throwable' => $e
            ],400);
        }

    }

    public function login(array $data)
    {
        $user = User::where('email', Arr::get($data, 'email'))->first();

        if (empty($user)) {
            throw new InvalidCredentialException;
        }

        if (!Hash::check(
            Arr::get($data, 'password'),
            Arr::get($user, 'password')
        )) {
            throw new InvalidCredentialException;
        }

        $token = auth('api')->attempt($data);

        // $token = Str::random(32);

        $user->update([
            'api_token' => hash('sha256', $token)
        ]);

        // return [
        //     'access_token' => $token
        // ];

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
            // 'secret' => $secret
        ];
        return $this->responseWithSuccess(
                "Authentication Success! and Direct Load User",
                $dataUser,
                201
        );
    }
}
