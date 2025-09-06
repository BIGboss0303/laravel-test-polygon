<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\User\UserResource;
use App\Http\Requests\Api\V1\User\RegisterRequest;
use App\Http\Resources\Api\V1\User\UserCollection;
use App\Http\Requests\Api\V1\User\StoreUserRequest;
use App\Http\Requests\Api\V1\User\UpdateUserRequest;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::get();
        return response()->json(new UserCollection($users));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();
        $validated['password'] = bcrypt($validated['password']);
        $user = User::create($validated);
        $user = $user->fresh();
        $user->sendEmailVerificationNotification();
        return response()->json(
            [
                'message' => "User registered successfully. Please verify your email",
                'user' => new UserResource($user)
            ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return response()->json(new UserResource($user));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update(['name' => $request->name]);
        return response()->json(new UserResource($user));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, 204);
    }

    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();
        $validated['password'] = bcrypt($validated['password']);
        $user = User::create($validated);
        $user = $user->fresh();
        $user->sendEmailVerificationNotification();
        return response()->json(
            [
                'message' => "User registered successfully. please verify your email",
                'user' => new UserResource($user),
                'token' => $user->createToken('auth-token')->plainTextToken
            ], 201);
    }
}
