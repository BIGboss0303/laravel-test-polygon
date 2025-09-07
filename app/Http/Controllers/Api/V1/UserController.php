<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Models\PhoneNumber;
use Illuminate\Support\Facades\DB;
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
        DB::connection('mysql')->beginTransaction();
        DB::connection('safe_mysql')->beginTransaction();
        try {
            $user = User::create($validated);
            $user = $user->fresh();
            PhoneNumber::create(['user_id' => $user->id, 'number' => $validated['number']]);
            $user->sendEmailVerificationNotification();
            DB::connection('safe_mysql')->commit();
            DB::connection('mysql')->commit();
            return response()->json(
                [
                    'message' => "User registered successfully",
                    'user' => new UserResource($user)
                ],
                201
            );
        } catch (\Exception $e) {
            DB::connection('safe_mysql')->rollBack();
            DB::connection('mysql')->rollBack();
            throw $e;
        }
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
        DB::connection('mysql')->beginTransaction();
        DB::connection('safe_mysql')->beginTransaction();
        try {
            $user->update(['name' => $request->name]);
            PhoneNumber::updateOrCreate(['user_id' => $user->id], ['number' => $request->number]);
            DB::connection('safe_mysql')->commit();
            DB::connection('mysql')->commit();
            return response()->json(new UserResource($user));
        } catch (\Exception $e) {
            DB::connection('safe_mysql')->rollBack();
            DB::connection('mysql')->rollBack();
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        DB::connection('mysql')->beginTransaction();
        DB::connection('safe_mysql')->beginTransaction();
        try {
            PhoneNumber::where('user_id', $user->id)->delete();
            $user->delete();
            DB::connection('safe_mysql')->commit();
            DB::connection('mysql')->commit();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            DB::connection('safe_mysql')->rollBack();
            DB::connection('mysql')->rollBack();
            throw $e;
        }
    }

    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();
        $validated['password'] = bcrypt($validated['password']);
        DB::connection('mysql')->beginTransaction();
        DB::connection('safe_mysql')->beginTransaction();
        try {
            $user = User::create($validated);
            $user = $user->fresh();
            PhoneNumber::create(['user_id' => $user->id, 'number' => $validated['number']]);
            $user->sendEmailVerificationNotification();
            DB::connection('safe_mysql')->commit();
            DB::connection('mysql')->commit();
            return response()->json(
                [
                    'message' => "User registered successfully. please verify your email",
                    'user' => new UserResource($user),
                    'token' => $user->createToken('auth-token')->plainTextToken
                ],
                201
            );
        } catch (\Exception $e) {
            DB::connection('safe_mysql')->rollBack();
            DB::connection('mysql')->rollBack();
            throw $e;
        }
    }

}
