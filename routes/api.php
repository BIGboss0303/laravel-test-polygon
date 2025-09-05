<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ResetPasswordController;
use App\Http\Controllers\Api\V1\EmailVerificationController;

Route::prefix('/v1')->group(function(){

    Route::middleware('auth:sanctum')->group(function(){

        Route::prefix('/auth')->controller(AuthController::class)->group(function(){
            Route::post('/login', 'login')->withoutMiddleware('auth:sanctum');
            Route::post('/logout', 'logout');
        });
        
        Route::prefix('/email')->controller(EmailVerificationController::class)->group(function(){
            Route::get('/verify', 'verify')->middleware('signed')->withoutMiddleware('auth:sanctum')->name('verification.verify');
            Route::post('/resend', 'resend')->middleware('throttle:3,1')->name('verification.resend');
            Route::get('/verify/notice', 'notice')->middleware('throttle:6,1')->name('verification.notice');
        });

        Route::prefix('/categories')->controller(CategoryController::class)->group(function(){
            Route::get('/','index');
            Route::get('/{category}','show')->whereNumber('category');
            Route::post('/','store');
            Route::put('/{category}','update')->whereNumber('category');
            Route::delete('/{category}','destroy')->whereNumber('category');
        });

        Route::prefix('/posts')->controller(PostController::class)->group(function(){
            Route::get('/','index');
            Route::get('/{post}','show')->whereNumber('post');
            Route::post('/','store');
            Route::put('/{post}','update')->whereNumber('post')->middleware('can:update,post');
            Route::delete('/{post}','destroy')->whereNumber('post')->middleware('can:delete,post');
        });
        
    });

    Route::prefix('/password')->controller(ResetPasswordController::class)->group(function(){
        Route::post('/forgot-password', 'forgot')->middleware(['guest', 'throttle:3,1'])->name('password.email');
        Route::post('/reset-password', 'reset')->middleware(['guest', 'throttle:3,1'])->name('password.update');
    });
    Route::post('/register', [UserController::class, 'register']);

});