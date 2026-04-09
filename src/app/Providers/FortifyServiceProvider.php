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
        Fortify::loginView(function () {
            return view('staff.auth.login');
        });

        Fortify::registerView(function () {
            return view('staff.auth.register');
        });

        Fortify::verifyEmailView(function () {
            return view('staff.auth.verify-email');
        });
    }
}
