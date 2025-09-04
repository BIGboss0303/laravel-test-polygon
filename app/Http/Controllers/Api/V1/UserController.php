<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\User\UserResource;
use App\Http\Requests\Api\V1\User\RegisterRequest;

class UserController extends Controller
{
    public function index()
    {
        $users = User::get();
        return $users;
    }

    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();
        $validated['password'] = bcrypt($validated['password']);
        $user = User::create($validated);
        $user = $user->fresh();
        return response()->json(
            [
                'message' => "User registered successfully.",
                'user' => new UserResource($user),
                'token' => $user->createToken('auth-token')->plainTextToken
            ], 201);
    }
}
