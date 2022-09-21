<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Login\LoginRequest;
use App\Services\Auth\AuthService;
class AuthController extends Controller
{
    public function __construct(private AuthService $authService)
    {
        //
    }

    public function login(LoginRequest $request)
    {
        return$this->authService->loginUser($request);
    }
}
