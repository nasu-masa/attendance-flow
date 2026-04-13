<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Models\User;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;

use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LoginViewResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest;
use Laravel\Fortify\Http\Responses\SimpleViewResponse;



class FortifyServiceProvider extends ServiceProvider
{
    public function register()
    {
        Fortify::ignoreRoutes();

        $this->app->bind(
            LoginRequest::class,
            LoginUserRequest::class
        );

        $this->app->singleton(CreatesNewUsers::class, CreateNewUser::class);

        $this->app->singleton(LoginViewResponse::class, function ($app) {
            return new SimpleViewResponse(function ($request) {
                return $request->is('admin/*') ? view('admin.auth.login') : view('staff.auth.login');
            });
        });

        $this->app->singleton(LogoutResponse::class, function ($app) {
            return new class implements LogoutResponse {
                public function toResponse($request)
                {
                    return str_contains($request->fullUrl(), 'admin')
                        ? redirect()->route('admin.login')
                        : redirect()->route('login');
                }
            };
        });
    }

    public function boot()
    {
        if (request()->route() && str_starts_with(request()->route()->getName(), 'admin.')) {
            logger()->info('Admin session cookie applied');
            config(['session.cookie' => config('session.cookie') . '_admin']);
        }

        Fortify::authenticateUsing(function ($request) {

            logger()->info('Fortify login attempt', [
                'path' => $request->path(),
                'is_admin_path' => $request->is('admin/*'),
                'email' => $request->email,
                'ip' => $request->ip(),
            ]);

            $loginRequest = app(LoginUserRequest::class);
            $loginRequest->merge($request->only('email', 'password'));
            $loginRequest->validateResolved();

            $user = User::where('email', $loginRequest->email)->first();

            if (!$user || !Hash::check($loginRequest->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['ログイン情報が登録されていません'],
                ]);
            }

            $isAdminPath = $request->is('admin', 'admin/*');
            $expectedRole = $isAdminPath ? User::ROLE_ADMIN : User::ROLE_STAFF;

            if ($user->role !== $expectedRole) {
                throw ValidationException::withMessages([
                    'email' => ['ログイン権限がありません'],
                ]);
            }

            if ($isAdminPath) {
                auth('admin')->login($user);
            } else {
                auth('web')->login($user);
            }

            return $user;
        });

        $this->app->instance(LoginResponse::class, new class implements LoginResponse {
            public function toResponse($request)
            {
                $user = auth()->user();

                session()->forget('url.intended');

                $target = ($user && $user->role === 'admin')
                    ? '/admin/attendance/list'
                    : '/attendance';

                return redirect()->to($target);

                logger()->info('Fortify login redirect', [
                    'user_id' => $user->id,
                    'role' => $user->role,
                    'redirect_to' => $target,
                ]);
            }
        });

        Fortify::loginView(function () {
            return request()->is('admin/*')
                ? view('admin.auth.login')
                : view('staff.auth.login');
        });

        Fortify::registerView(function () {
            return view('staff.auth.register');
        });

        Fortify::verifyEmailView(function () {
            return view('staff.auth.verify-email');
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });
    }
}
