<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\AuthLoginRequest;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class AuthController extends Controller
{
    /**
     * login
     * @unauthenticated
     */
    public function login(LoginRequest $request){
        $credentials = $request->validated();
        if(Auth::attempt($credentials)){
            $user = Auth::user();
            $user->tokens()->delete();
            $token = $user->createToken('auth-token')->plainTextToken;
            return response()->json(['token' => $token]);
        }
        return response()->json(['error' => 'Unauthorized', 'message' => 'wrong credentials'], 401);
    }

    /**
     * logout
     */
    public function logout(){
        $user = Auth::user();
        $user->tokens()->delete();
        return response(null, 200);
    }
}
