<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;

Route::prefix('/v1')->group(function(){

    Route::middleware('auth:sanctum')->group(function(){

        Route::prefix('/auth')->controller(AuthController::class)->group(function(){
            Route::post('/login', 'login')->withoutMiddleware('auth:sanctum');
            Route::post('/logout', 'logout');
        });     
        
    });

    Route::post('/register', [UserController::class, 'register']);

});