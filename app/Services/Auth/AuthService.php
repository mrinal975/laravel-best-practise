<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Http\Requests\Login\LoginRequest;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function __construct(private User $user)
    {
        //
    }

    public function loginUser(LoginRequest $request): User
    {
        $user = $this->userService->getByEmail($request->email);

        $credentials = $request->only('email', 'password');

        // Other all user login
        if (auth()->attempt($credentials)) {
            $token = auth()->user()->createToken('web_access_token')->accessToken;
            $user = Auth::user();
            $user->access_token = $token;
        } else {
        }
    }
}