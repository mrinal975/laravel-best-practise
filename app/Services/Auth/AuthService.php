<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Http\Requests\Login\LoginRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Auth\AuthLoginResource;
use App\Engine\HttpStatus;

class AuthService
{

    public function __construct(private User $user)
    {
        $this->user = $user;
    }

    public function loginUser(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        // Other all user login
        if (auth()->attempt($credentials)) {
            $user = $this->getUserData();
            return response()->json(['data' => $user, HttpStatus::STATUS => HttpStatus::OK], HttpStatus::OK);
        } else {
            return response()->json(['error' => 'UnAuthorised', HttpStatus::STATUS => HttpStatus::UNAUTHORIZED], HttpStatus::OK);
        }
    }

    public function getUserData(): AuthLoginResource
    {
        $token = auth()->user()->createToken('web_access_token')->accessToken;
        $user = Auth::user();
        $user->access_token = $token;
        $user = new AuthLoginResource($user);
        $user['status'] = HttpStatus::OK;
        return $user;
    }
}