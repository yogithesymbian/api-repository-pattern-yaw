<?php


namespace App\Http\Controllers;

use App\Services\JsonApiAdapter;
use App\Services\Users\LoginRequest;
use App\Services\Users\RegisterRequest;
use App\Services\Users\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * @var UserRepository
     */
    private $repository;

    /**
     * @var JsonApiAdapter
     */
    private $adapter;

    public function __construct(
        UserRepository $repository,
        JsonApiAdapter $adapter
    )
    {
        $this->repository = $repository;
        $this->adapter = $adapter;
    }

    public function show(Request $request)
    {
        return $this->adapter
            ->render($request->user());
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

    public function register(RegisterRequest $request)
    {
        return $user = $this->repository
            ->register(
                $request->validated()
            );
    }

    public function login(LoginRequest $request)
    {
        return $this->repository
            ->login(
                $request->validated()
            );
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
