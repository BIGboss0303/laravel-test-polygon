<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmailNotification extends VerifyEmail
{
    protected function verificationUrl($notifiable)
    {        
        $expires = Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60))->toDateTimeString();
        return url(route('frontend.verify')) . '?' . http_build_query([
            'id' => $notifiable->getKey(),
            'signature' => Hash::make($notifiable->getEmailForVerification() . $expires . env('APP_KEY')),
            'expires' => $expires
        ]);
    }
}
