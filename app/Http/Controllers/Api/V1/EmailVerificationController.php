<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EmailVerificationController extends Controller
{
    public function verify(Request $request)
    {
        $user = User::find($request->id);
        $signature = $request->signature;
        $expires = $request->expires;
        
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if (!Hash::check($user->email . $expires . env("APP_KEY"), $signature)) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }
        
        if(Carbon::translateTimeString($expires) <= now()){
            return response()->json(['message' => 'Link is expired'], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email is already verified'], 200);
        }

        $user->markEmailAsVerified();

        return response()->json(['message' => 'Email is verified'], 200);
    }

    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified'], 422);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification email sent']);
    }

    public function notice(){
        $user = Auth::user();
        if ($user->hasVerifiedEmail()){
            return response()->json([
                'verified' => true,
                'message' => 'Email is verified'
            ], 200);
        }
        else{
            return response()->json([
                'verified' => false,
                'message' => 'Please, verify your email'
            ], 200);
        }
    }
}
