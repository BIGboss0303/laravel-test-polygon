<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //Ссылка на сброс пароля (указывает на фронтэнд)
        ResetPassword::createUrlUsing(function ($user, string $token) {
            return url(route('frontend.reset-password')) . '?' . http_build_query([
                'token' => $token,
                'email' => $user->email,
            ]);
        });
    }
}
