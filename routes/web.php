<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ResetPasswordController;
use App\Http\Controllers\Api\V1\EmailVerificationController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/reset-password', function(Request $request){
    $controller = app(ResetPasswordController::class);
    $response = $controller->reset(new Request([
        'token' => $request->token,
        'email' => $request->email,
        'password' => 'password',
        'password_confirmation' => 'password'
    ]));
    return response()->json($response);
})->name('frontend.reset-password');

Route::get('/verify-email', function(Request $request){
    $controller = app(EmailVerificationController::class);
    $response = $controller->verify(new Request([
        'id' => $request->id,
        'signature' => $request->signature,
        'expires' => $request->expires,
    ]));
    return response()->json($response);
})->name('frontend.verify');