<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // ログイン画面
        Fortify::loginView(function () {
            return view('staff.auth.login');
        });

        // 登録画面
        Fortify::registerView(function () {
            return view('staff.auth.register');
        });

        // メール認証（必要なら）
        Fortify::verifyEmailView(function () {
            return view('staff.auth.verify-email');
        });
    }
}
