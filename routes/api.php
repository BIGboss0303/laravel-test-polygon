<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\ResetPasswordController;

Route::prefix('/v1')->group(function(){

    Route::middleware('auth:sanctum')->group(function(){

        Route::prefix('/auth')->controller(AuthController::class)->group(function(){
            Route::post('/login', 'login')->withoutMiddleware('auth:sanctum');
            Route::post('/logout', 'logout');
        });     
        
    });

    Route::prefix('/password')->controller(ResetPasswordController::class)->group(function(){
        Route::post('/forgot-password', 'forgot')->middleware(['guest', 'throttle:3,1'])->name('password.email');
        Route::post('/reset-password', 'reset')->middleware(['guest', 'throttle:3,1'])->name('password.update');
    });
    Route::post('/register', [UserController::class, 'register']);

});