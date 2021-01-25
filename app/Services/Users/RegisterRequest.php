<?php


namespace App\Services\Users;


use App\Http\Requests\RequestValidation;

class RegisterRequest extends RequestValidation
{

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'user_photo' => 'required|string',
        ];
    }
}
