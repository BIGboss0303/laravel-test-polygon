<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ResetPasswordController;

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