<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Services\AuthService;

class AuthController extends Controller
{
    /* ================================
        PG01：会員登録画面
    ================================= */

    public function store(RegisterUserRequest $request, AuthService $service)
    {
        $user = $service->registerStaff($request->validated());

        Auth::login($user);

        $user->sendEmailVerificationNotification();

        return view('staff.auth.verify-email');
    }

    /* ================================
        PG02：ログイン画面
    ================================= */

    public function login(LoginUserRequest $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return back()->withErrors([
                'email' => 'ログイン情報が登録されていません',
            ]);
        }

        if (!Auth::user()->isStaff()) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'ログイン権限がありません',
            ]);
        }

        $request->session()->regenerate();

        return redirect()
            ->route('staff.attendance.index')
            ->with('success', 'ログインが成功しました');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
